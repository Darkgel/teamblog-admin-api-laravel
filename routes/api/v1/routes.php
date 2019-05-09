<?php
/**
 * Created by PhpStorm.
 * User: Darkgel
 * Date: 2018/12/29
 * Time: 15:38
 */

use Dingo\Api\Routing\Router;

/** @var Dingo\Api\Routing\Router $api*/
$api->version('v1', ['namespace' => 'App\Api\Controllers\V1', 'middleware' => ['api.common']], function (Router $api) {
    $api->group(['namespace' => 'Authorization', 'prefix' => 'authorization'], function (Router $api){
        $api->group(['prefix' => 'permissions'], function (Router $api){
            $api->get('/', 'PermissionController@index');
            $api->get('{id}', 'PermissionController@detail');
            $api->post('/', 'PermissionController@save');
        });

        $api->group(['prefix' => 'roles'], function (Router $api){
            $api->get('/', 'RoleController@index');
            $api->get('{id}', 'RoleController@detail');
            $api->post('/', 'RoleController@save');
            $api->post('/with-permissions', 'RoleController@saveWithPermissions');
        });

        $api->group(['prefix' => 'users'], function (Router $api){
            $api->get('/', 'UserController@index');
            $api->get('{id}', 'UserController@detail');
            $api->post('/', 'UserController@save');
            $api->post('/with-authorization', 'UserController@saveWithAuthorization');
        });

        $api->group(['prefix' => 'services'], function (Router $api){
            $api->get('/', 'ServiceController@index');
            $api->get('{id}', 'ServiceController@detail');
            $api->post('/', 'ServiceController@save');
        });
    });

    $api->group(['prefix' => 'tmp'], function (Router $api){
        $api->get('user', 'TmpController@testUser');
    });
});
