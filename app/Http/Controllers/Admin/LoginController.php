<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{

    /** 获取验证码
     * @return mixed
     */
    public function captcha()
    {
        $data['url'] = app('captcha')->create('default', true);
        return code_response(10,'获取验证码成功',200,$data);
    }

    /** 用户登陆
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $input = $request->except(['captcha','catKey']);

        //字段验证
        $validator = Validator::make($input, [
            "username"=>"required|min:2|max:16",
            "password"=>"required|between:4,20",
        ],[
            'username.required' => '账号不能为空',
            'username.min' => '账号最短2位',
            'username.max' => '账号最长16位',
            'password.required' => '密码不能为空',
            'password.between' => '密码长度不符合规定',
        ]);

        //查看字段是否错误
        if ($validator->fails())
        {
            $warnings = $validator->messages()->first();
            return code_response(10005,$warnings);
        }

        //验证图片操作
//        if (!captcha_api_check($request->captcha, $request->catKey)){
//            return code_response(10006,'图片验证码错误');
//        }

        //账号密码验证
        if(!$token = Auth::guard('admin')->attempt($input)){
            return code_response(10004,'账号或密码错误');
        };

        $data['token'] = 'Bearer '. $token;

        //更新用户登陆次数、更新用户最新登陆时间
        $username = $request->input('username');
        $admin = Admin::where('username',$username)->first();
        $admin->num = $admin->num + 1;
        $admin->admin_time = date('Y-m-d H:i:s');
        $admin->save();

        //记录登陆日志
        \Log::info($username.":登陆成功");
        return code_response(10,'登陆成功',200,$data);
    }

    /** 退出登陆
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        Auth::guard('admin')->logout();
        return code_response(10,'退出成功');
    }
}