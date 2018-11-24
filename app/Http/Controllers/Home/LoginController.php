<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $input = $request->all();
        if(!$token = auth()->guard('admin')->attempt($input)){
            return response()->json(['status'=>'0','message' => '邮箱或密码错误.']);
        };
//        $user = Admin::where('admin_name','zhangsan')->first();
//        if(Hash::check($request->input('password'),$user->password)){
//            $token = $this->auth->fromUser($user);
//        }else{
//            return response()->json(['status'=>'0','message' => '登陆失败']);
//        }
        return response()->json(['status'=>'1','token' =>'Bearer '. $token]);
    }
}