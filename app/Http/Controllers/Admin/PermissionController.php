<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{

    /** 获取角色列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(){
        $datas = Permission::all();
        //web数据处理
        if(!$datas->isEmpty()){
            $permission = $this->getTree($datas, 0);
            $data['permission'] = $permission;
        }else{
            $data['permission'] = [];
        }
        return code_response(10,'获取权限列表成功',200,$data);
    }

    /** 处理权限数据
     * @param $data
     * @param $parent_id
     * @return array
     */
    private function getTree($data,$parent_id)
    {
        $tree = [];
        if(!empty($data)){
            foreach($data as $k => $v)
            {
                if($v->parent_id == $parent_id)
                {
                    $result['id'] = $v->id;
                    $result['name'] = $v->name;
                    $result['access'] = false;
                    $result['display_name'] = $v->display_name;
                    $children  =$this->getTree($data, $v->id);
                    if(!empty($children)){
                        $result['children'] = $children;
                    }
                    array_push($tree,$result);
                }
            }
        }
        return $tree;
    }

    /** 修改或者新增角色权限
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request)
    {
        $role_id = $request->input('role_id');
        $data = $request->input('data');

        //1.判断角色是否存在
        $role = Role::where('id',$role_id)->first();
        if(!$role){
            return code_response(20009,'角色不存在');
        }

        //2.如果角色，有权限（删除权限）
        PermissionRole::where('role_id',$role_id)->delete();
        //3.给角色赋予新权限
        if(!empty($data)) {
            $parent_id = [];
            $reslt = [];
            foreach ($data as $item) {
                $premiss = Permission::where('name', $item)->first();
                if ($premiss->parent_id != 0) {
                    $pidname = Permission::find($premiss->parent_id)->toArray();
                    if (!in_array($pidname['name'], $data) && !in_array($premiss->parent_id, $parent_id)) {
                        $arr['role_id'] = $role_id;
                        $arr['permission_id'] = $premiss->parent_id;
                        array_push($reslt, $arr);
                        array_push($parent_id, $premiss->parent_id);
                    }
                }
                if (!in_array($premiss->id, $parent_id)) {
                    $arr['role_id'] = $role_id;
                    $arr['permission_id'] = $premiss->id;
                    array_push($reslt, $arr);
                    array_push($parent_id, $premiss->id);
                }
            }
            $save_permiss = PermissionRole::insert($reslt);
            if(!$save_permiss){
                return code_response(20010,'权限添加或者修改失败');
            }
        }

        return code_response(10,'权限添加或者修改成功');
    }

    /** 查看用户权限
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request)
    {
        $role_id = $request->input('role_id');
        $role = Role::where('id',$role_id)->first();
        if(!$role){
            return code_response(20011,'角色信息获取失败');
        }
        $ids = PermissionRole::where('role_id',$role_id)->pluck('permission_id')->toArray();
        $permissions = Permission::whereIn('id',$ids)->get();
        //处理数据为前台所需类型
        $data = $this->getPermission($permissions,0);
        return code_response(10,'获取角色权限成功',200,$data);
    }

    /** 处理权限数据
     * @param $data
     * @param $parent_id
     * @return array
     */
    private function getPermission($data,$parent_id)
    {
        $tree = [];
        if(!empty($data)){
            foreach($data as $k => $v)
            {
                if($v->parent_id == $parent_id)
                {
                    $result['name'] = $v->name;
                    $result['meta'] = unserialize($v->meta);
                    $children  =$this->getPermission($data, $v->id);
                    if(!empty($children)){
                        $result['children'] = $children;
                    }
                    array_push($tree,$result);
                }
            }
        }
        return $tree;
    }
}