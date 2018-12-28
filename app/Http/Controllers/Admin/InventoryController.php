<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeList;
use Auth;
use Validator;
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
        //搜索内容
        $search = $request->input('search');

        //判断供应链可查看产品数据
        $admin = Auth::guard('admin')->user();

        //根据组ID获取组成员用户ID
        $admin_ids = Admin::where('admin_group',$admin->admin_group)->pluck('id')->toArray();

        //根据用户所在组id获取产品列表
        $list = Product::whereIn('id',$admin_ids)->where('name','like','%'.$search.'%')->get();

        return code_response(10, '获取产品列表成功！', 200, $list);
    }

    /**
     * 查看产品属性信息
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
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
                    $attr = [];
                    foreach ($attribute_list_ids as $attribute_list_id){
                        $attribute_list = ProductAttributeList::where('id',$attribute_list_id)->first();
                        if($attribute_list){
                            $attribute_list_title[] = $attribute_list->attributeListHasAttribute->english_name;
                            $attr[$attribute_list->attributeListHasAttribute->english_name] = $attribute_list->attribute_value;
                        }
                    }
                    $attr['inventory'] = $item->num;
                    $attr['price'] = $item->price;
                    $attr['sku'] = $item->sku;
                    array_push($productAttribute,$attr);
                    $title = array_merge($attribute_list_title,['inventory','price','sku']);
                }else{
                    $title = ['inventory','price','sku'];
                }
            }
        }

        $data['title'] = $title;
        $data['data'] = $productAttribute;
        return code_response(10, '获取产品列表成功！', 200, $data);
    }

    /**
     * 修改库存（可批量修改）
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request)
    {
        $data = $request->all();

        //字段验证
        $validator = Validator::make($data, [
            'ids' => 'required',
            'num' => 'required|numeric',
            ]);

        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }

        //获取更改sku的
        $ids = explode(',',$data['ids']);

        $bool = ProductAttribute::whereIn('id',$ids)->update(['num'=>$data['num']]);

        if($bool) {
            return code_response(10, '修改库存成功！');
        } else {
            return code_response(50001, '修改库存失败！');
        }
    }
}
