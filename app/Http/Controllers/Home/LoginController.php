<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    /** 登陆
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->all();
        if(!$token = auth()->guard('admin')->attempt($input)){
            return response()->json(['status'=>'0','message' => '邮箱或密码错误.']);
        };
        return response()->json(['status'=>'1','token' =>'Bearer '. $token]);
    }
}