<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class SqlLogMiddleware extends BaseMiddleware
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
        //获取用户信息
        $data = $request->user_info;
        $admin_user =  $data->show_name ? $data->show_name : $data->username;

        //记录sql语句
        \DB::listen(
            function ($sql) use ($request,$admin_user) {
                $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);
                $query = vsprintf($query, $sql->bindings);
                $route = $request->path();
                if('select' != substr($query,0,6)){
                    $monolog = Log::getMonolog();
                    $monolog->popHandler();
                    Log::useDailyFiles(storage_path('logs/sql/sql.log'));
                    Log::info('操作人：'. $admin_user .'；操作人IP：'.$request->ip().'；操作路由：'.$route.'；sql语句：'.$query);
                }
            }
        );
        return $next($request);
    }
}
