<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\RoleAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    /** 获取角色列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(){
        $data = Role::all();
        return code_response(10,'获取角色列表成功',200,$data);
    }

    /** 添加角色
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request)
    {
        $role_name = $request->input('role_name');
        if(!trim($role_name)){
            return code_response(20000,'请填写角色名称');
        }

        //1.角色名称不可重复
        $role = Role::where('name',$role_name)->first();
        if($role){
            return code_response(20001,'角色名称已存在');
        }

        //2.新增角色
        $role=new Role();
        $role->name=$request->input('role_name');
        $msg=$role->save();

        if($msg){
            return code_response(10,'角色添加成功');
        }else{
            return code_response(20002,'角色添加失败');
        }
    }

    /** 修改角色
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $role_id = $request->input('role_id');

        //角色名称是否填写
        $role_name = $request->input('role_name');
        if(!trim($role_name)){
            return code_response(20003,'请填写角色名称');
        }

        //1.角色名称不可重复
        $role_name = Role::where('name',$request->input('role_name'))->first();
        if($role_name){
            return code_response(20004,'角色名称已存在');
        }

        //2.修改角色
        $role = Role::where('id',$role_id)->first();
        $role->name=$request->input('role_name');
        $msg=$role->save();

        if($msg){
            return code_response(10,'角色修改成功');
        }else{
            return code_response(20005,'角色修改失败');
        }
    }

    /** 删除角色
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destory(Request $request)
    {
        $role_id = $request->input('role_id');

        //判断该角色下，是否有用户存在
        $role_user = RoleAdmin::where('role_id',$role_id)->first();
        if($role_user){
            return code_response(20006,'该角色下有用户存在，不可删除');
        }

        try {
            DB::beginTransaction();

            //删除角色
            $role = Role::where('id',$role_id)->delete();
            if(!$role){
                throw new \Exception('角色删除失败！');
            }

            //删除角色权限
            $permission_role = PermissionRole::where('role_id',$role_id)->first();
            if($permission_role){
                $permiss_role = PermissionRole::where('role_id',$role_id)->delete();
                if(!$permiss_role){
                    throw new \Exception('角色权限删除失败！');
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            return code_response(20007,$e->getMessage());
        }

        return code_response(10,'角色删除成功');
    }

    /** 获取角色信息
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        $role_id = $request->input('role_id');
        $role = Role::where('id',$role_id)->first();
        if($role){
            return code_response(10,'角色信息获取成功',200,$role);
        }else{
            return code_response(20008,'角色不存在');
        }
    }

}