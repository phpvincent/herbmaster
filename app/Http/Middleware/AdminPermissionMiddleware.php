<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;

class AdminPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //1. 获取用户权限
        $user = $request->user_info;

        //2. 获取访问路径
        $path = $request->getRequestUri();
        $path_array = explode('/',$path);
        $permissions = \DB::table('role_user')->leftjoin('permission_role','permission_role.role_id','=','role_user.role_id')->leftjoin('permissions','permissions.id','=','permission_role.permission_id')->where('role_user.user_id',$user->id)->where('permissions.name',$path_array[2])->first();

        //判断是否有访问权限
        if(!$permissions){
            return code_response(20012,'抱歉，您没有该接口的访问权限');
        }

        return $next($request);
    }
}
