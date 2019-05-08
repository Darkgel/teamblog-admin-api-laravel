<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 15:19
 */

namespace App\Api\Controllers\V1\Authorization;


use App\Api\Controllers\V1\V1Controller;
use App\Exceptions\BusinessException;
use App\Repositories\Passport\RoleRepository;
use App\Transformers\Authorization\RoleTransformer;
use Dingo\Api\Http\Request;
use Enum\ErrorCode;

class RoleController extends V1Controller
{
    public function detail(){

    }

    public function index(RoleRepository $roleRepository, Request $request){
        try{
            $pageNum = intval($request->query('pageNum', 1));
            $pageSize = intval($request->query('pageSize', 15));

            $cacheKey = __METHOD__."_"."pageNum:".$pageNum."_"."pageSize:".$pageSize;
            if(\Cache::has($cacheKey)){
                $content = \Cache::get($cacheKey);
                return $this->response->array($content);
            }
            $roles = $roleRepository->getRoles($pageNum, $pageSize);

            return $this->response
                ->paginator($roles, new RoleTransformer())
                ->header(self::CACHE_KEY_AND_TIME_HEADER, [$cacheKey]);

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    public function save(RoleRepository $roleRepository, Request $request){
        try{
            // 校验数据有效性
            $postData = $request->post();

            $role = $roleRepository->save($postData);

            if(!is_null($role)){//业务逻辑执行成功
                return $this->response->item($role, new RoleTransformer());
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

    public function saveWithPermissions(RoleRepository $roleRepository, Request $request){
        try{
            // 校验数据有效性
            $postData = $request->post();

            $role = $roleRepository->saveWithPermissions($postData);

            if(!is_null($role)){//业务逻辑执行成功
                return $this->response->item($role, new RoleTransformer());
            }else{
                throw new BusinessException(ErrorCode::BUSINESS_SERVER_ERROR);
            }

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }
}
