<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 17:50
 */

namespace App\Repositories\Passport;


use App\Exceptions\SystemException;
use App\Models\DbPassport\User;
use Carbon\Carbon;
use Enum\ErrorCode;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    protected $dbPassportConnection = 'db_passport';
    protected $associationTableUserPermission = 'authorization_user_permissions';
    protected $associationTableUserRole = 'authorization_role_users';

    public function save($userData) {
        if(empty($userData['id']) || intval($userData['id']) < 1){// 新建user
            $model = User::getDefaultInstance();
        } else {
            $model = User::find(intval($userData['id']));
        }

        if(!empty($userData['id'])) unset($userData['id']);
        $model->fill($userData);

        if(!empty($userData['password'])){
            $model->password = \Hash::make($userData['password']);
        }

        return ($model->save()) ? $model : null;
    }

    /**
     * @param int $pageNum
     * @param int $pageSize
     *
     * @return LengthAwarePaginator
     */
    public function getUsers($pageNum, $pageSize){
        $models = User::orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'pageNum', $pageNum);

        return $models;
    }

    public function saveWithPermissionsAndRoles($data){
        try {
            $permissionIdArray = [];
            $roleIdArray = [];
            \DB::connection($this->dbPassportConnection)->beginTransaction();
            if(isset($data['permissions'])){
                $permissionIdArrayTmp = explode(',', $data['permissions']);
                foreach($permissionIdArrayTmp as $permissionIdString){
                    $permissionIdArray[] = intval($permissionIdString);
                }
                unset($data['permissions']);
            }

            if(isset($data['roles'])){
                $roleIdArrayTmp = explode(',', $data['roles']);
                foreach($roleIdArrayTmp as $roleIdString){
                    $roleIdArray[] = intval($roleIdString);
                }
                unset($data['roles']);
            }

            $user = $this->save($data);

            if(!is_null($user)){
                $this->associateUserWithPermissions($user, $permissionIdArray);
                $this->associateUserWithRoles($user, $roleIdArray);
                \DB::connection($this->dbPassportConnection)->commit();
                return $user;
            }

            throw new SystemException(ErrorCode::SYSTEM_DB_WRITE_ERROR);

        } catch (\Exception $e){
            try{
                \DB::connection($this->dbPassportConnection)->rollBack();
            }catch(\Exception $e){
                //记录日志
                \Log::error($e->getMessage(), $e->getTrace());
            }

            return null;
        }
    }

    public function associateUserWithPermissions($user, $permissionIdArray){
        //先获得已经与该user关联的permission id
        $associatedPermissionIdsResult  = \DB::connection($this->dbPassportConnection)
            ->table($this->associationTableUserPermission)
            ->where('user_id', $user->id)
            ->get(['permission_id'])->toArray();

        $associatedPermissionsIdArray = [];
        foreach ($associatedPermissionIdsResult as $item){
            $associatedPermissionsIdArray[] = $item->permission_id;
        }

        //需要添加的关联关系
        $addAssociatedPermissionIdArray = array_diff($permissionIdArray, $associatedPermissionsIdArray);
        if(count($addAssociatedPermissionIdArray) > 0){
            $insertAssociation = [];
            foreach($addAssociatedPermissionIdArray as $id){
                $insertAssociation[] = [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'user_id' => $user->id,
                    'permission_id' => \intval($id),
                ];
            }
            \DB::connection($this->dbPassportConnection)
                ->table($this->associationTableUserPermission)
                ->insert($insertAssociation);
        }

        //需要删除的关联关系
        $deleteAssociatedPermissionIdArray = array_diff($associatedPermissionsIdArray, $permissionIdArray);
        if(count($deleteAssociatedPermissionIdArray) > 0){
            \DB::connection($this->dbPassportConnection)
                ->table($this->associationTableUserPermission)
                ->where('user_id', $user->id)
                ->whereIn('permission_id', $deleteAssociatedPermissionIdArray)
                ->delete();
        }
    }

    public function associateUserWithRoles($user, $roleIdArray){
        //先获得已经与该user关联的permission id
        $associatedRoleIdsResult  = \DB::connection($this->dbPassportConnection)
            ->table($this->associationTableUserRole)
            ->where('user_id', $user->id)
            ->get(['role_id'])->toArray();

        $associatedRolesIdArray = [];
        foreach ($associatedRoleIdsResult as $item){
            $associatedRolesIdArray[] = $item->permission_id;
        }

        //需要添加的关联关系
        $addAssociatedRoleIdArray = array_diff($roleIdArray, $associatedRolesIdArray);
        if(count($addAssociatedRoleIdArray) > 0){
            $insertAssociation = [];
            foreach($addAssociatedRoleIdArray as $id){
                $insertAssociation[] = [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'user_id' => $user->id,
                    'role_id' => \intval($id),
                ];
            }
            \DB::connection($this->dbPassportConnection)
                ->table($this->associationTableUserRole)
                ->insert($insertAssociation);
        }

        //需要删除的关联关系
        $deleteAssociatedRoleIdArray = array_diff($associatedRolesIdArray, $roleIdArray);
        if(count($deleteAssociatedRoleIdArray) > 0){
            \DB::connection($this->dbPassportConnection)
                ->table($this->associationTableUserRole)
                ->where('user_id', $user->id)
                ->whereIn('role_id', $deleteAssociatedRoleIdArray)
                ->delete();
        }
    }
}
