<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Admin;

class UserController extends Controller
{
    public function profile()
    {
        $admin = new Admin();
        $admin->admin_name = 'zhaoliu';
        $admin->password = bcrypt('123456');
        $admin->is_root = '1';
        $admin->admin_show_name = 'lisi';
        if($admin->save()){
            return response(['status'=>'1','message'=>'添加成功']);
        }else{
            return response(['status'=>'0','message'=>'添加失败']);
        }
    }
}
