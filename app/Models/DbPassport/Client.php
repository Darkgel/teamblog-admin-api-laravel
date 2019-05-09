<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 11:08
 */

namespace App\Models\DbPassport;


class Client extends \Laravel\Passport\Client
{
    protected $connection = 'db_passport';
}
