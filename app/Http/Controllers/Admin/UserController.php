<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UserController extends Controller
{

    /** 获取验证码
     * @return mixed
     */
    public function userInfo()
    {
        return response()->json(['status_code'=>'1','message' =>'created succeed','url'=> app('captcha')->create('default', true)]);
    }

}