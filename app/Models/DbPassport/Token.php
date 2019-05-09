<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 11:08
 */

namespace App\Models\DbPassport;

class Token extends \Laravel\Passport\Token
{
    protected $connection = 'db_passport';
}
