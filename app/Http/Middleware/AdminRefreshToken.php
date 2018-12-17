<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

// 注意，我们要继承的是 jwt 的 BaseMiddleware
class AdminRefreshToken extends BaseMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {   
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $authToken = Auth::guard('admin')->getToken();
        if(!$authToken){
            throw new UnauthorizedHttpException('jwt-auth', 'Token not provided');
        }
        // 检测用户的登录状态，如果正常则通过
        if ($user=Auth::guard('admin')->check($authToken)) {
            if($user->admin_method!=1){
                //在只读权限下进行的写操作
                $request_method = $request->getMethod();
                    if(!in_array($request_method, ['get','post'])){
                        return code_response(10003,'Request Methoud not allow',405);
                    }
            }
            return $next($request);
        }
        // 使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
        try {
            if($token = Auth::guard('admin')->refresh()){
                $request->headers->set('Authorization', 'Bearer '.$token);
            }
        } catch (JWTException $exception) {
            // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
            throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
        }

        // 在响应头中返回新的 token
        $respone = $next($request);
        if(isset($token) && $token){
            $respone->headers->set('Authorization', 'Bearer '.$token);
        }
        return $respone;
    }
}
