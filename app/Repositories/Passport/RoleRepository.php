<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 15:53
 */

namespace App\Repositories\Passport;


use App\Exceptions\SystemException;
use App\Models\DbPassport\Role;
use App\Repositories\AppRepository;
use Carbon\Carbon;
use Enum\ErrorCode;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository extends AppRepository
{
    protected $dbPassportConnection = 'db_passport';
    protected $associationTableRolePermission = 'authorization_role_permissions';

    public function save($roleData) {
        if(empty($roleData['id']) || intval($roleData['id']) < 1){// 新建role
            $model = Role::getDefaultInstance();
        } else {
            $model = Role::find(intval($roleData['id']));
        }

        if(!empty($roleData['id'])) unset($roleData['id']);
        $model->fill($roleData);

        return ($model->save()) ? $model : null;
    }

    /**
     * @param int $pageNum
     * @param int $pageSize
     *
     * @return LengthAwarePaginator
     */
    public function getRoles($pageNum, $pageSize){
        $models = Role::orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'pageNum', $pageNum);

        return $models;
    }

    public function saveWithPermissions($data){
        try {
            $permissionIdArray = [];
            \DB::connection($this->dbPassportConnection)->beginTransaction();
            if(isset($data['permissions'])){
                $permissionIdArrayTmp = explode(',', $data['permissions']);
                foreach($permissionIdArrayTmp as $permissionIdString){
                    $permissionIdArray[] = intval($permissionIdString);
                }
                unset($data['permissions']);
            }
            $role = $this->save($data);

            if(!is_null($role)){
                $this->associateRoleWithPermissions($role, $permissionIdArray);
                \DB::connection($this->dbPassportConnection)->commit();
                return $role;
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

    public function associateRoleWithPermissions($role, $permissionIdArray){
        //先获得已经与该user关联的permission id
        $associatedPermissionIdsResult  = \DB::connection($this->dbPassportConnection)
            ->table($this->associationTableRolePermission)
            ->where('role_id', $role->id)
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
                    'role_id' => $role->id,
                    'permission_id' => \intval($id),
                ];
            }
            \DB::connection($this->dbPassportConnection)
                ->table($this->associationTableRolePermission)
                ->insert($insertAssociation);
        }

        //需要删除的关联关系
        $deleteAssociatedPermissionIdArray = array_diff($associatedPermissionsIdArray, $permissionIdArray);
        if(count($deleteAssociatedPermissionIdArray) > 0){
            \DB::connection($this->dbPassportConnection)
                ->table($this->associationTableRolePermission)
                ->where('role_id', $role->id)
                ->whereIn('permission_id', $deleteAssociatedPermissionIdArray)
                ->delete();
        }
    }
}
