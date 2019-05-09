<?php
/**
 * Created by PhpStorm.
 * User: michael.shi
 * Date: 2019/5/9
 * Time: 15:40
 */

namespace App\Http\Middleware;

use App\Models\DbPassport\User;
use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ApiAuthorization
{
    public function handle($request, Closure $next, ...$args){
        // 获取请求对应的user
        /** @var User $user */
        $user = \Auth::guard('api')->user();
        if($this->shouldPassThrough($request)){//跳过检验
            return $next($request);
        }

        if (!$user->allServicePermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        })) {
            throw new UnauthorizedHttpException('unauthorized!');
        }

        return $next($request);
    }


    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = config('authorization.api.excepts', []);

        return collect($excepts)
            ->map(function($path = ''){
                $prefix = '/'.trim(config('admin.route.prefix'), '/');

                $prefix = ($prefix == '/') ? '' : $prefix;

                $path = trim($path, '/');

                if (is_null($path) || strlen($path) == 0) {
                    return $prefix ?: '/';
                }

                return $prefix.'/'.$path;

            })->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                return $request->is($except);
            });
    }
}
