<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductAttribute;
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
        $datas = ProductAttribute::where('product_id',$id)->get();

        $title = [];
        $productAttribute = [];
        if(!$datas->isEmpty()){
            foreach ($datas as &$item){
                $attribute_list_ids = explode(',',$item->attribute_list_ids);
                $attribute_list_title = [];
                if(count($attribute_list_ids) > 0){
                    foreach ($attribute_list_ids as $attribute_list_id){
                        $attribute_list = ProductAttribute::where('id',$attribute_list_id)->first();
                        if($attribute_list){
                            $attribute_list_title[] = $attribute_list->hass->name;
                        }
//                        $attribute_list_title = ProductAttribute::where('product_attribute_list.id',$attribute_list_id)->leftJoin('attributes','attributes.id','=','product_attribute_list.attribute_id')->value('attributes.name');
                    }
                    $title = array_merge($attribute_list_title,['库存','价格','SKU']);
                }else{
                    $title = ['库存','价格','SKU'];
                }
            }
        }

        $data['title'] = $title;
        return code_response(10, '获取产品列表成功！', 200, $data);
    }
}
