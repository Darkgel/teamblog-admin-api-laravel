<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 11:09
 */

namespace App\Models\DbPassport;


class PersonalAccessClient extends \Laravel\Passport\PersonalAccessClient
{
    protected $connection = 'db_passport';
}
