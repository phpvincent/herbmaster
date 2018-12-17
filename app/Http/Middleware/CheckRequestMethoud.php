<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
class CheckRequestMethoud
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
        if(Auth::guard('admin')->user()->admin_method!=1){
                    $request_method = $request->getMethod();
                    if(!in_array($request_method, ['get','post'])){
                        return response('Request Methoud not allow','405');
                    }
        }

        return $next($request);
    }
}
