<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/4/2
 * Time: 17:40
 */

namespace App\Api\Controllers\V1\Authorization;

use App\Api\Controllers\V1\V1Controller;
use App\Exceptions\BusinessException;
use App\Repositories\Passport\UserRepository;
use App\Transformers\Authorization\UserTransformer;
use Dingo\Api\Http\Request;
use Enum\ErrorCode;

class UserController extends V1Controller
{
    /**
     * 创建用户，于授权无关
     */
    public function save(UserRepository $userRepository, Request $request){
        try{
            // 校验数据有效性
            $postData = $request->post();
            $permission = $userRepository->save($postData);

            if(!is_null($permission)){//业务逻辑执行成功
                return $this->response->item($permission, new UserTransformer());
            }else{
                throw new BusinessException(ErrorCode::BUSINESS_SERVER_ERROR);
            }

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    public function index(UserRepository $userRepository, Request $request){
        try{
            $pageNum = intval($request->query('pageNum', 1));
            $pageSize = intval($request->query('pageSize', 15));

            $cacheKey = __METHOD__."_"."pageNum:".$pageNum."_"."pageSize:".$pageSize;
            if(\Cache::has($cacheKey)){
                $content = \Cache::get($cacheKey);
                return $this->response->array($content);
            }
            $permissions = $userRepository->getUsers($pageNum, $pageSize);

            return $this->response
                ->paginator($permissions, new UserTransformer())
                ->header(self::CACHE_KEY_AND_TIME_HEADER, [$cacheKey]);

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    /**
     * 为用户授权
     */
    public function authorizeUser(){

    }

    /**
     * 保存用户，并且会保存相应的授权信息
     */
    public function saveWithAuthorization(UserRepository $userRepository, Request $request){
        try{
            // 校验数据有效性
            $postData = $request->post();

            $user = $userRepository->saveWithPermissionsAndRoles($postData);

            if(!is_null($user)){//业务逻辑执行成功
                return $this->response->item($user, new UserTransformer());
            }else{
                throw new BusinessException(ErrorCode::BUSINESS_SERVER_ERROR);
            }

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }
}
