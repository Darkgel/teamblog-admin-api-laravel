<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 15:53
 */

namespace App\Repositories\Passport;


use App\Models\DbPassport\Permission;
use App\Repositories\AppRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionRepository extends AppRepository
{
    public function save($permissionData) {
        if(empty($permissionData['id']) || intval($permissionData['id']) < 1){// 新建permission
            $model = Permission::getDefaultInstance();
        } else {
            $model = Permission::find(intval($permissionData['id']));
        }

        if(!empty($permissionData['id'])) unset($permissionData['id']);
        $model->fill($permissionData);

        return ($model->save()) ? $model : null;
    }

    /**
     * @param int $pageNum
     * @param int $pageSize
     *
     * @return LengthAwarePaginator
     */
    public function getPermissions($pageNum, $pageSize){
        $models = Permission::orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'pageNum', $pageNum);

        return $models;
    }
}
