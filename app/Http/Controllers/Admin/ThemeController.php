<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ThemeController extends Controller
{
    public function index(Request $request)
    {

    }

    public function info(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'theme_id' => 'required|integer',
            'page_name' => 'required|in:blog,cart,cart_gift,collection_list,collection_page,home,not_found,page,product',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $theme = DB::table('theme_' . $request->input('page_name'))->where('site_id', $request->input('site_id'))->where('theme_id', $request->input('theme_id'))->first();

        if ($theme) {
            return code_response(10, '获取主题数据成功', 200, ['data' => $theme]);
        } else {
            return code_response(40401, '暂无数据');
        }
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'theme_id' => 'required|integer',
            'page_name' => 'required|in:blog,cart,cart_gift,collection_list,collection_page,home,not_found,page,product',
            'type' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $table = 'theme_' . $request->input('page_name');
        $data = ['site_id' => $request->input('site_id'),
            'theme_id' => $request->input('theme_id'),
            'type' => $request->input('type'), 'content' => $request->input('content'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            ];
        if ($theme = DB::table($table)->insert($data)) {
            return code_response(10, '添加成功！', 200, ['data' => DB::table($table)->where('site_id', $request->input('site_id'))->where('theme_id', $request->input('theme_id'))->first()]);
        } else {
            return code_response(50001, '添加失败！');
        }
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
//            'site_id' => 'required|exists:sites,id',
//            'theme_id' => 'required|integer',
            'id' => 'required|integer',
            'page_name' => 'required|in:blog,cart,cart_gift,collection_list,collection_page,home,not_found,page,product',
            'type' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $table = 'theme_' . $request->input('page_name');
        if (!DB::table($table)->where('id', $request->input('id'))->exists()) {
            return code_response(40401, '该页面主题不存在');
        }
        $data = ['type' => $request->input('type'), 'content' => $request->input('content'),'updated_at' => Carbon::now()];
        if (DB::table($table)->where('id', $request->input('id'))->update($data)) {
            return code_response(10, '修改成功！', 200, ['data' => DB::table($table)->where('id', $request->input('id'))->first()]);
        } else {
            return code_response(50001, '修改失败！');
        }
    }
}
