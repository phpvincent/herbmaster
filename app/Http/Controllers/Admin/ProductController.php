<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeList;
use App\Models\Resource;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
//            'name' => 'required|string|unique:products,name|max:64',
            'name' => ['required', 'string', 'max:64', Rule::unique('products')->where('site_id', $request->input('site_id'))],
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
        $attribute_options = [];
        $product_attributes = [];
        if ($request->has('attribute_options') && !empty($request->input('attribute_options'))) {
            $attribute_options = json_decode($request->input('attribute_options'));
            $check_attribute_values = $this->check_attribute_values($attribute_options);
            if ($check_attribute_values !== true) {
                return code_response(10002, $check_attribute_values['msg']);
            }
            if (empty($request->input('product_attributes'))) {
                return code_response(10003, '请设置产品Variants！');
            }
            $product_attributes = json_decode($request->input('product_attributes'));
        }
        try {
            DB::beginTransaction();
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
                if ($attribute_options) {
                    $product_attribute_list = [];
                    foreach ($attribute_options as $attribute_id => $options) {
                        foreach ($options as $value) {
                            $options = new ProductAttributeList();
                            $options->attribute_id = $attribute_id;
                            $options->product_id = $product->id;
                            $options->attribute_value = $value->name;
                            $options->attribute_english_value = $value->english_name;
                            $options->save();
                            $product_attribute_list[$attribute_id . '_' . $value->name] = $options->id;
                        }
                    }
                }
                //添加产品变种
                if ($product_attributes) {
                    foreach ($product_attributes as $attribute) {
                        $product_attribute = new ProductAttribute();
                        $product_attribute->product_id = $product->id;
                        $product_attribute->attribute_list_ids = $this->get_product_attribute_ids($attribute, $product_attribute_list);
                        $product_attribute->price = isset($attribute->price) ? $attribute->price : $product->price;
                        $product_attribute->sku = isset($attribute->sku) ? $attribute->sku : $product->sku;
                        $product_attribute->bar_code = isset($attribute->bar_code) ? $attribute->bar_code : $product->bar_code;
                        $product_attribute->num = isset($attribute->num) ? $attribute->num : $product->num;
                        $product_attribute->save();
                    }
                }
                if ($request->has('product_resources') && !empty($request->input('product_resources'))) {
                    $product_resources = json_decode($request->input('product_resources'));
                    if ($product_resources) {
                        foreach ($product_resources as $resource) {
                            DB::table('product_resource')->insert(['product_id' => $product->id, 'resource_id' => $resource->resource_id, 'is_index' => $resource->is_index, 'sort' => $resource->sort]);
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
            }
            DB::commit();
        } catch (\Exception $e) {
            return code_response(50001, '添加产品失败！' . $e->getMessage());
        }

        return code_response(10, '添加产品成功！', 200, ['data' => $product]);

    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'id' => 'required|integer|exists:products,id',
            'name' => ['required', 'string', 'max:64', Rule::unique('products')->where('site_id', $request->input('site_id'))->ignore($request->input('id'))],
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
        $product = Product::find($request->input('id'));
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
            return code_response(10, '修改产品成功！', 200, ['data' => $product]);
        }else{
            return code_response(50001, '修改产品失败！');
        }
    }

    public function add_variant(Request $request)
    {

    }

    public function destory($id)
    {
        if (!$id || !Product::where('id', $id)->exists()) {
            return code_response(10001, '该产品不存在失败！');
        }
        if(Product::destroy($id)) {
            return code_response(10, '删除产品成功！');
        }else{
            return code_response(50001, '删除产品失败！');
        }
    }
    public function info($id)
    {
        $product = Product::with('resources', 'attributes')->find($id);
        $product->attribute_values = Product::attribute_values($id);
        $product->product_attributes = Product::product_attribute_list($id);
        return code_response(10, '获取产品信息成功！', 200, ['data' => $product]);
    }

    private function check_attribute_values($attribute_values)
    {
        foreach ($attribute_values as $attribute_id => $options) {
            if (!Attribute::where('id', $attribute_id)->exists()) {
                return ['code' => 0, 'msg' => '该属性不存在！'];
            }
            foreach ($options as $value) {
                if (!isset($value->name) || !isset($value->english_name)) {
                    return ['code' => 0, 'msg' => '属性值及英文属性值均不能为空!'];
                }
            }
        }
        return true;
    }

    private function get_product_attribute_ids($attributes, $product_attribute_list)
    {
        $ids = [];
        foreach ($attributes as $key => $value) {
            if (isset($product_attribute_list[$key . '_' . $value])) {
                $ids[] = $product_attribute_list[$key . '_' . $value];
            }
        }
        return implode(',', $ids);
    }
}
