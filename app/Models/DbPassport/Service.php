<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 14:17
 */

namespace App\Models\DbPassport;

/**
 * @property int $id
 * @property string $name 服务名
 * @property string $description 服务描述
 */
class Service extends BaseModel
{
    protected $table = 'authorization_services';

    protected $fillable = ['name', 'description'];

    /**
     * @return static
     */
    public static function getDefaultInstance(){
        $model = new static;
        $model->name = '';
        $model->description = '';

        return $model;
    }
}
