<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/3
 * Time: 10:14
 */

namespace App\Api\Controllers\V1;


use Laravel\Passport\Guards\TokenGuard;

class TmpController extends V1Controller
{
    public function testUser(){
        $tmp = '';

        $guard = \Auth::guard('api');

        $user = $guard->user();

        $client = $guard->client();

        dd($user, $client);
    }
}
