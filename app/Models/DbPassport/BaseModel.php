<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/3/27
 * Time: 9:29
 */

namespace App\Models\DbPassport;


use App\Models\AppModel;

class BaseModel extends AppModel
{
    protected $connection = 'db_passport';
}
