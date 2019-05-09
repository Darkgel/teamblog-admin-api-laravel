<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 14:16
 */

namespace App\Repositories\Passport;


use App\Models\DbPassport\Service;
use App\Repositories\AppRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceRepository extends AppRepository
{
    public function save($data) {
        if(empty($data['id']) || intval($data['id']) < 1){
            $model = Service::getDefaultInstance();
        } else {
            $model = Service::find(intval($data['id']));
        }

        if(!empty($data['id'])) unset($data['id']);
        $model->fill($data);

        return ($model->save()) ? $model : null;
    }

    /**
     * @param int $pageNum
     * @param int $pageSize
     *
     * @return LengthAwarePaginator
     */
    public function getServices($pageNum, $pageSize){
        $models = Service::orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'pageNum', $pageNum);

        return $models;
    }
}
