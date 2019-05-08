<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 15:18
 */

namespace App\Api\Controllers\V1\Authorization;


use App\Api\Controllers\V1\V1Controller;
use App\Exceptions\BusinessException;
use App\Repositories\Passport\PermissionRepository;
use App\Transformers\Authorization\PermissionTransformer;
use Dingo\Api\Http\Request;
use Enum\ErrorCode;

class PermissionController extends V1Controller
{
    public function test(){
        try{
            $cacheKey = __METHOD__;
            if(\Cache::has($cacheKey)){
                $content = \Cache::get($cacheKey);
                return $this->response->array($content);
            }

            return $this->response
                ->array(['test' => 'abc'])
                ->header(self::CACHE_KEY_AND_TIME_HEADER, [$cacheKey]);

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    public function detail(){

    }

    public function index(PermissionRepository $permissionRepository, Request $request){
        try{
            $pageNum = intval($request->query('pageNum', 1));
            $pageSize = intval($request->query('pageSize', 15));

            $cacheKey = __METHOD__."_"."pageNum:".$pageNum."_"."pageSize:".$pageSize;
            if(\Cache::has($cacheKey)){
                $content = \Cache::get($cacheKey);
                return $this->response->array($content);
            }
            $permissions = $permissionRepository->getPermissions($pageNum, $pageSize);

            return $this->response
                ->paginator($permissions, new PermissionTransformer())
                ->header(self::CACHE_KEY_AND_TIME_HEADER, [$cacheKey]);

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    public function save(PermissionRepository $permissionRepository, Request $request){
        try{
            // 校验数据有效性
            $postData = $request->post();

            $permission = $permissionRepository->save($postData);

            if(!is_null($permission)){//业务逻辑执行成功
                return $this->response->item($permission, new PermissionTransformer());
            }else{
                throw new BusinessException(ErrorCode::BUSINESS_SERVER_ERROR);
            }

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    public function delete(){

    }
}
