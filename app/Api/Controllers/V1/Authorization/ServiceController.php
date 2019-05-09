<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 14:13
 */

namespace App\Api\Controllers\V1\Authorization;


use App\Exceptions\BusinessException;
use App\Models\DbPassport\Service;
use App\Repositories\Passport\ServiceRepository;
use App\Transformers\Authorization\ServiceTransformer;
use Enum\ErrorCode;
use Illuminate\Http\Request;

class ServiceController extends BaseController
{
    public function index(ServiceRepository $serviceRepository, Request $request){
        try{
            $pageNum = intval($request->query('pageNum', 1));
            $pageSize = intval($request->query('pageSize', 15));

            $cacheKey = __METHOD__."_"."pageNum:".$pageNum."_"."pageSize:".$pageSize;
            if(\Cache::has($cacheKey)){
                $content = \Cache::get($cacheKey);
                return $this->response->array($content);
            }
            $permissions = $serviceRepository->getServices($pageNum, $pageSize);

            return $this->response
                ->paginator($permissions, new ServiceTransformer())
                ->header(self::CACHE_KEY_AND_TIME_HEADER, [$cacheKey]);

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }

    public function save(ServiceRepository $serviceRepository, Request $request){
        try{
            // 校验数据有效性
            $postData = $request->post();
            $permission = $serviceRepository->save($postData);

            if(!is_null($permission)){//业务逻辑执行成功
                return $this->response->item($permission, new ServiceTransformer());
            }else{
                throw new BusinessException(ErrorCode::BUSINESS_SERVER_ERROR);
            }

        } catch (BusinessException $e) {
            return $this->response->array($e->getExtra())
                ->header(self::BUSINESS_STATUS_HEADER, [$e->getCode(), $e->getMessage()]);
        }
    }
}
