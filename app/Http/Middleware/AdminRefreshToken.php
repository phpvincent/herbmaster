<?php

//namespace App\Http\Middleware;
//
//use Auth;
//use Closure;
//use Tymon\JWTAuth\Exceptions\JWTException;
//use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
//use Tymon\JWTAuth\Exceptions\TokenExpiredException;
//use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
//
//// 注意，我们要继承的是 jwt 的 BaseMiddleware
//class AdminRefreshToken extends BaseMiddleware
//{
//    protected $guard = 'admin';
//    /**
//     * @param $request
//     * @param Closure $next
//     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
//     * @throws JWTException
//     */
//    public function handle($request, Closure $next)
//    {
////        \Config::set('jwt.user', 'App\Admin');
////        \Config::set('auth.providers.users.model', \App\Admin::class);
////        \Config::set('auth.providers.admins.model', \App\Admin::class);
////        config('jwt.user' , "App\Admin");
////        config('auth.defaults.guard', 'admin');
////        config('auth.providers.users.model', \App\Admin::class);
////        config('auth.providers.admins.model', \App\Admin::class);
//        //        config('auth.defaults.guard', 'admin');
//                $authToken = Auth::guard('admin')->getToken();
//
////return code_response('1111',config('jwt.user'));
////return code_response('1111',$this->auth->parseToken()->authenticate());
////return code_response('2222',Auth::guard('admin')->check());
//return code_response('2222',Auth::guard('admin')->payload()['sub']);
////return code_response('2222',Auth::guard('admin')->checkOrFail());
//        // 检查此次请求中是否带有 token，如果没有则抛出异常。
//        $this->checkForToken($request);
////        return response(['status'=>$this->auth->root()]) ;
//        // 使用 try 包裹，以捕捉 token 过期所抛出的 TokenExpiredException  异常
//        try {
//            // 检测用户的登录状态，如果正常则通过
//            if ($this->auth->parseToken()->authenticate()) {
//                return $next($request);
//            }
//            throw new UnauthorizedHttpException('jwt-auth', '未登录');
//        } catch (TokenExpiredException $exception) {
//            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
//            try {
//                // 刷新用户的 token
//                $token = $this->auth->refresh();
//                // 使用一次性登录以保证此次请求的成功
//                Auth::guard('admin')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
//            } catch (JWTException $exception) {
//                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
//                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
//            }
//        }
//
//        // 在响应头中返回新的 token
//        return $this->setAuthenticationHeader($next($request), $token);
//    }
//}

namespace App\Http\Middleware;

use App\Models\Admin;
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
        dd('?');
        // 检查此次请求中是否带有 token，如果没有则抛出异常。
        $authToken = Auth::guard('admin')->getToken();
        if(!$authToken){
            return code_response(10001, 'Token not provided', 401);
        }

        // 检测用户的登录状态，如果正常则通过
        if (Auth::guard('admin')->check()) {
            $admin_id = Auth::guard('admin')->payload()['sub'];
            $time = Auth::guard('admin')->payload()['exp'];
            $user = Admin::where('id', $admin_id)->first();

            //==========================================================================================================
            //判断用户操作权限
            if ($user && $user->admin_method != 1) {
                //在只读权限下进行的写操作
                $request_method = $request->getMethod();
                if ($request_method!='get') {
                    if($request_method=='post'){
                       if(!$request->has('forread')) return code_response(10003, 'Request Methoud not allow', 405);
                    }else{
                        return code_response(10003, 'Request Methoud not allow', 405);
                    }
                }
            }

            //设置用户请求参数
            $request->user_info = $user;

            //==============================================================================================================
            //刷新Token
            if(($time - time()) < 10*60 && ($time - time()) > 0){
                $token = Auth::guard('admin')->refresh();
                if(!$token){
                    $request->headers->set('Authorization', 'Bearer '.$token);
                }else{
                    return code_response(10002, 'The token has been blacklisted', 401);
                }

                // 在响应头中返回新的 token
                $respone = $next($request);
                if(isset($token) && $token){
                    $respone->headers->set('Authorization', 'Bearer '.$token);
                }
                return $respone;
            }

            //token通过验证 执行下一补操作
            return $next($request);
        }

        return code_response(10002, 'The token has been blacklisted', 401);
    }
}
