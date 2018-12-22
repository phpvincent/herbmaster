<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\AdminGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * 获取管理员列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $search = $request->has('search') ? $request->input('search') : null;
        $is_root = $request->has('is_root') ? $request->input('is_root') : false;
        $admin_use = $request->has('admin_use') ? $request->input('admin_use') : false;
        $admin_group = $request->input('admin_group', 0);
        $per_page = $request->input('per_page', 15);
        $admins = Admin::with(['group:id,group_name,group_rule'])->where(function ($query) use ($search) {
            if ($search) {
                $query->where('username', 'like', '%' . $search . '%')->orWhere('show_name', 'like', '%' . $search . '%');
            }
        })->where(function ($query) use ($is_root) {
            if ($is_root !== false) {
                $query->where('is_root', $is_root);
            }
        })->where(function ($query) use ($admin_use) {
            if ($admin_use !== false) {
                $query->where('admin_use', $admin_use);
            }
        })->where(function ($query) use ($admin_group) {
            if ($admin_group) {
                $query->where('admin_group', $admin_group);
            }
        })->paginate($per_page);
        return code_response(10, '获取成功！', 200, ['data' => $admins]);
    }

    /** 获取当前管理员信息
     * @param
     * @return \Illuminate\Http\JsonResponse
     */
    public function my()
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return code_response(50001, '获取当前管理员信息失败！');
        } else {
            $admin->group = AdminGroup::select('group_name', 'group_rule')->find($admin->admin_group);
            return code_response(10, '获取当前管理员信息成功！', 200, ['data' => $admin]);
        }
    }

    /** 获取管理员信息
     * @param id
     * @return 管理员信息
     */
    public function info($id)
    {
        if (!is_numeric($id) || !$id) {
            return code_response(10001, '参数错误！');
        }
        $admin = Admin::with(['group:id,group_name,group_rule'])->find($id);
        if (!$admin) {
            return code_response(50001, '获取管理员信息失败！');
        } else {
            return code_response(10, '获取管理员信息成功！', 200, ['data' => $admin]);
        }
    }

    /**
     * 添加管理员
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|:admins|max:100',
            'password' => 'required|string|between:6,20',
            'show_name' => 'required|string|max:255',
            'is_root' => 'required|in:0,1',
            'admin_use' => 'required|in:0,1',
            'admin_group' => 'required|exists:admin_group,id',
            'admin_method' => 'required|in:0,1',
            'is_order' => 'required|in:0,1'
            ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $admin = new Admin();
        $admin->username = $request->input('username');
        $admin->password = bcrypt($request->input('password'));
        $admin->show_name = $request->input('show_name');
        $admin->is_root = $request->input('is_root');
        $admin->admin_use = $request->input('admin_use');
        $admin->admin_group = $request->input('admin_group');
        $admin->admin_method = $request->input('admin_method');
        $admin->is_order = $request->input('is_order');
        $admin->admin_rule = $request->input('admin_rule',0);
        $admin->admin_languages = $request->input('admin_languages', 0);
        if($admin->save()) {
            return code_response(10, '添加管理员成功！', 200, ['data' => $admin]);
        } else {
            return code_response(50001, '添加管理员失败！');
        }
    }

    /**
     * 修改管理员密码
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|between:6,20',
            'new_password' => 'required|between:6,20|confirmed',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        if(!Hash::check(($request->input('old_password')), $admin->password)){
            return code_response(10002, '原密码输入错误！');
        }
        $admin->password = bcrypt($request->input('new_password'));
        if($admin->save()){
            return code_response(10, '修改管理员密码成功！');
        }else {
            return code_response(50001, '修改管理员密码失败！');
        }
    }

    /**
     * 修改管理员信息
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'exists:admins,id',
            'username' => 'required|unique:admins,username,'. $request->input('id').'|max:100',
            'show_name' => 'required|max:255',
            'is_root' => 'required|in:0,1',
            'admin_use' => 'required|in:0,1',
            'admin_group' => 'required|exists:admin_group,id',
            'admin_method' => 'required|in:0,1',
            'is_order' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $admin = Admin::find($request->input('id'));
        $admin->username = $request->input('username');
        $admin->show_name = $request->input('show_name');
        $admin->is_root = $request->input('is_root');
        $admin->admin_use = $request->input('admin_use');
        $admin->admin_group = $request->input('admin_group');
        $admin->admin_method = $request->input('admin_method');
        $admin->is_order = $request->input('is_order');
        $admin->admin_rule = $request->input('admin_rule',0);
        $admin->admin_languages = $request->input('admin_languages', 0);
        if($admin->save()) {
            return code_response(10, '修改管理员成功！', 200, ['data' => $admin]);
        } else {
            return code_response(50001, '修改管理员失败！');
        }
    }

    /**
     *   删除管理员
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destory($id)
    {
        $validator = Validator::make([$id], [
            'id' => 'exists:admins,id']);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        if(Admin::destroy($id)) {
            return code_response(10, '删除管理员成功！');
        } else {
            return code_response(50001, '删除管理员失败！');
        }
    }
}
