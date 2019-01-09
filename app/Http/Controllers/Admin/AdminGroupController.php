<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\AdminGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AdminGroupController extends Controller
{
    /**
     * 获取管理员组列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function all()
    {
        $groups = AdminGroup::all();
        return code_response(10, '获取管理员组成功！ ', 200, ['data' => $groups]);
    }

    /**
     * 获取管理员组详情
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function info(Request $request)
    {
        $id = $request->input('id');
        if (!is_numeric($id) || !$id) {
            return code_response(10001, '参数错误！');
        }
        $group = AdminGroup::find($id);
        if (!$group) {
            return code_response(50001, '获取管理员组信息失败！');
        } else {
            return code_response(10, '获取管理员组信息成功！', 200, ['data' => $group]);
        }
    }

    /**
     * 添加管理员组
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), ['group_name' => 'required|unique:admin_group|max:255', 'group_rule' => 'required|in:0,1']);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $group = new AdminGroup();
        $group->group_name = $request->input('group_name');
        $group->group_rule = $request->input('group_rule');
        if ($group->save()) {
            return code_response(10, '添加管理员组成功！', 200, ['data' => $group]);
        } else {
            return code_response(50001, '添加管理员组失败！');
        }

    }

    /**
     * 修改管理员组
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request)
    {
        $id = $request->input('id');
        $validator = Validator::make($request->all(), [
            'id' => 'exists:admin_group,id',
            'group_name' => 'required|unique:admin_group,group_name,' . $id . '|max:255',
            'group_rule' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $group = AdminGroup::find($request->input('id'));
        $group->group_name = $request->input('group_name');
        $group->group_rule = $request->input('group_rule');
        if ($group->save()) {
            return code_response(10, '修改管理员组成功！', 200, ['data' => $group]);
        } else {
            return code_response(50001, '修改管理员组失败！');
        }
    }

    /**
     *  删除管理员组
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destory(Request $request)
    {
        $id = $request->input('id');
        $validator = Validator::make([$id], [
            'id' => 'exists:admin_group,id']);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        if (Admin::where('admin_group', $id)->exists()) {
            return code_response(60001, '该管理员组存在管理员不允许删除！');
        }
        if(AdminGroup::destroy($id)) {
            return code_response(10, '删除管理员组成功！');
        } else {
            return code_response(50001, '删除管理员组失败！');
        }

    }
}
