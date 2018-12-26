<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductAttributeList;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $site_id = $request->input('site_id', 0);
        $search = $request->input('search', '');
        $type = $request->input('type', 0);
        $status = $request->has('status') ? $request->input('status') : false;
        $per_page = $request->input('per_page', 15);
        $product = Product::where('site_id', $site_id)->where(function ($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', '%' . $search . '%')->orWhere('english_name', 'like', '%' . $search . '%');
            }
        })->where(function ($query) use ($type) {
            if ($type) {
                $query->where('type', $type);
            }
        })->where(function ($query) use ($status) {
            if ($status !== false) {
                $query->where('status', $status);
            }
        })->paginate($per_page);

        return code_response(10, '获取产品列表成功！', 200, ['data' => $product]);
    }

    public function add(Request $request)
    {
//        $data = [1 => [['name' => '红色', 'english_name' => 'red'], ['name' => '蓝色', 'english_name' => 'blue']],2=>[['name' => 32,'english_name' =>32]]];
//        dd(json_encode($data));
//        exit();
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|unique:products,name|max:64',
            'english_name' => 'required|string|max:64',
            'description' => 'required',
            'price' => 'required|numeric',
            'cost_price' => 'required|numeric',
            'original_price' => 'required|numeric',
            'is_reduce_invenory' => 'required|in:0,1',
            'num' => 'required|integer',
            'is_physical_product' => 'required|in:0,1',
            'weight' => 'required|numeric',
            'type' => 'required|exists:product_types,id',
            'collection_id' => 'required|integer',
            'status' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $product = new Product();
        $product->site_id = $request->input('site_id');
        $product->name = $request->input('name');
        $product->english_name = $request->input('english_name');
        $product->description = $request->input('description');
        $product->price = $request->input('price', 0);
        $product->original_price = $request->input('original_price', 0);
        $product->cost_price = $request->input('cost_price', 0);
        $product->is_reduce_invenory = $request->input('is_reduce_invenory');
        $product->num = $request->input('num', 0);
        $product->is_physical_product = $request->input('is_physical_product');
        $product->weight = $request->input('weight');
        $product->type = $request->input('type');
        $product->collection_id = $request->input('collection_id');
        $product->admin_id = Auth::guard('admin')->id();
        $product->status = $request->input('status');
        if ($product->save()) {
            //添加属性信息
            if ($request->has('attribute_options') && !empty($request->input('attribute_options'))) {
//                dd(json_decode($request->input('attribute_options')));
                $attribute_options = json_decode($request->input('attribute_options'));
//                dd($attribute_options);
                foreach ($attribute_options as $attribute_id => $options) {
                   foreach ($options as $value) {
                       $options = new ProductAttributeList();
                       $options->attribute_id = $attribute_id;
                       $options->product_id = $product->id;
                       $options->attribute_value = $value->name;
                       $options->attribute_english_value = $value->english_name;
                       $options->save();
                   }
                }
            }
            // 添加供货商
            if ($request->has('supplier_url') && $request->input('supplier_url')) {
                $supplier = new  Supplier();
                $supplier->product_id = $product->id;
                $supplier->url = $request->input('supplier_url');
                $supplier->contact = $request->input('supplier_contact', '');
                $supplier->phone = $request->input('supplier_phone', '');
                $supplier->price = $request->input('supplier_price', 0);
                $supplier->num = $request->input('supplier_num', 0);
                $supplier->remark = $request->input('remark', '');
                $supplier->save();
            }
            return code_response(10, '添加产品成功！', 200, ['data' => $product]);
        } else {
            return code_response(50001, '添加产品失败！');
        }

    }
}
