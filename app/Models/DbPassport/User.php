<?php

namespace App\Models\DbPassport;

use App\Models\AppModelTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Laravel\Passport\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $emailVerifiedAt
 * @property string $password
 * @property string $rememberToken
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $deletedAt
 * @property integer $type
 * @property Role[] | Collection roles
 * @property Permission[] | Collection permissions
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, AppModelTrait;

    protected $connection = 'db_passport';

    protected $table = 'users';

    const TYPE_USER = 0;//代表具体用户，默认值
    const TYPE_CLIENT = 1;//代表某种client

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'rememberToken',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'emailVerifiedAt' => 'datetime',
    ];

    /**
     * @return static
     */
    public static function getDefaultInstance(){
        $model = new static;

        $model->name = '';
        $model->email = '';
        $model->password = '';
        $model->type = self::TYPE_USER;

        return $model;
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(){
        $pivotTable = 'authorization_role_users';
        $relatedModel = Role::class;
        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions()
    {
        $pivotTable = 'authorization_user_permissions';
        $relatedModel = Permission::class;
        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    public function allPermissions()
    {
        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->merge($this->permissions);
    }

    /**
     * Get all service permissions of user.
     *
     * @return mixed
     */
    public function allServicePermissions()
    {
        $service = Service::where('name', config('app.service_name', 'admin'))->first();

        return $this->roles()->with([
            'permissions' => function ($query) use ($service) {
                return $query->where('service_id', $service->id);
            }
        ])->get()->pluck('permissions')->flatten()->merge(
            $this->permissions()->where('service_id', $service->id)->get()
        );
    }

    /**
     * Check if user has permission.
     *
     * @param $ability
     * @param array $arguments
     *
     * @return bool
     */
    public function can($ability, $arguments = []) : bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->permissions->pluck('slug')->contains($ability)) {
            return true;
        }

        return $this->roles->pluck('permissions')->flatten()->pluck('slug')->contains($ability);
    }

    /**
     * Check if user has no permission.
     *
     * @param $ability
     * @param array $arguments
     *
     * @return bool
     */
    public function cannot($ability, $arguments = []) : bool
    {
        return !$this->can($ability, $arguments = []);
    }

    /**
     * Check if user is administrator.
     *
     * @return mixed
     */
    public function isAdministrator() : bool
    {
        return $this->isRole('administrator');
    }

    /**
     * Check if user is $role.
     *
     * @param string $role
     *
     * @return mixed
     */
    public function isRole(string $role) : bool
    {
        return $this->roles->pluck('slug')->contains($role);
    }

    /**
     * Check if user in $roles.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []) : bool
    {
        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * If visible for roles.
     *
     * @param $roles
     *
     * @return bool
     */
    public function visible(array $roles = []) : bool
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles) || $this->isAdministrator();
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->roles()->detach();

            $model->permissions()->detach();
        });
    }
}
