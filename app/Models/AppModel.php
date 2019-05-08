<?php
/**
 * 该应用中所有model的基类，在往该类添加代码时需谨慎
 *
 * Created by PhpStorm.
 * User: Darkgel
 * Date: 2018/12/29
 * Time: 15:28
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class AppModel extends Model
{
    use AppModelTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

}
