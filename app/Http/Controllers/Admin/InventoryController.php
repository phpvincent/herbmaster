<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InventoryController extends Controller
{

    /**
     * 库存页产品列表
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        //判断供应链可查看产品数据
        $admin = Auth::guard('admin')->user();

        //搜索条件（）
        $list = Product::whereNull('deleted_at')->get();
        return code_response(10, '获取产品列表成功！', 200, $list);
    }

    public function show(Request $request)
    {
        //获取产品id
        $id = $request->input('id');

        //返回产品属性
        $data = 'aaa';
    }
}
