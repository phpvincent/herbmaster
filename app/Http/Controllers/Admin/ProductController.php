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
                            if(ProductAttributeList::where('product_id', $product->id)->where('attribute_id', $attribute_id)->where('attribute_value',$value->attribute_value)->exist()) {
                                continue;
                            }
                            $options = new ProductAttributeList();
                            $options->attribute_id = $attribute_id;
                            $options->product_id = $product->id;
                            $options->attribute_value = $value->attribute_value;
                            $options->attribute_english_value = $value->attribute_english_value;
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
            DB::rollBack();
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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'price' => 'required|numeric',
            'sku' => 'required|string|max:32',
            'bar_code' => 'required|string|max:32',
            'num' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        if (! $request->has('attribute_list')) {
            return code_response(10002, '请输入产品属性选项及值！');
        }
        $attribute_list = json_decode($request->input('attribute_list'));
        $ids = [];
        foreach ($attribute_list as $key=>$value) {
            $id =  ProductAttributeList::where('product_id', $request->input('product_id'))->where('attribute_id', $key)->where('attribute_value', $value->attribute_value)->value('id');
            if(! $id) {
                $product_attribute_list = new ProductAttributeList();
                $product_attribute_list->product_id = $request->input('product_id');
                $product_attribute_list->attribute_id = $key;
                $product_attribute_list->attribute_value = $value->attribute_value;
                $product_attribute_list->attribute_english_value = $value->attribute_english_value;
                $product_attribute_list->save();
                $ids[] = $product_attribute_list->id;
            } else {
                $ids[] = $id;
            }
        }
        $product_attribute = new  ProductAttribute();
        $product_attribute->product_id = $request->input('product_id');
        $product_attribute->attribute_list_ids = implode(',', $ids);
        $product_attribute->price = $request->input('price');
        $product_attribute->sku = $request->input('sku');
        $product_attribute->bar_code = $request->input('bar_code');
        $product_attribute->num = $request->input('num');
        if ($product_attribute->save()) {
            return code_response(10, '添加产品变种成功！', 200, ['data' => $product_attribute]);
        }else{
            return code_response(50001, '修改产品变种失败！');
        }
    }
    public function edit_variant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:product_attributes,id',
            'price' => 'required|numeric',
            'sku' => 'required|string|max:32',
            'bar_code' => 'required|string|max:32',
            'num' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        if (! $request->has('attribute_list')) {
            return code_response(10002, '请输入产品属性选项及值！');
        }
        $attribute_list = json_decode($request->input('attribute_list'));
        $ids = [];
        foreach ($attribute_list as $key=>$value) {
            $id =  ProductAttributeList::where('product_id', $request->input('product_id'))->where('attribute_id', $key)->where('attribute_value', $value->attribute_value)->value('id');
            if(! $id) {
                $product_attribute_list = new ProductAttributeList();
                $product_attribute_list->product_id = $request->input('product_id');
                $product_attribute_list->attribute_id = $key;
                $product_attribute_list->attribute_value = $value->attribute_value;
                $product_attribute_list->attribute_english_value = $value->attribute_english_value;
                $product_attribute_list->save();
                $ids[] = $product_attribute_list->id;
            } else {
                $ids[] = $id;
            }
        }
        $product_attribute = ProductAttribute::find($request->input('id'));
        $product_attribute->attribute_list_ids = implode(',', $ids);;
        $product_attribute->price = $request->input('price');
        $product_attribute->sku = $request->input('sku');
        $product_attribute->bar_code = $request->input('bar_code');
        $product_attribute->num = $request->input('num');
        if ($product_attribute->save()) {
            return code_response(10, '修改产品变种成功！', 200, ['data' => $product_attribute]);
        }else{
            return code_response(50001, '修改产品变种失败！');
        }

    }
    public function add_option(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
//            'attribute_id' => 'required|integer|exists:attributes,id',
//            'attribute_value' => ['required', 'string', 'max:64', Rule::unique('product_attribute_list')
//                ->where('product_id', $request->input('product_id'))->where('attribute_id', $request->input('attribute_id'))],
//            'attribute_english_value' => 'required|string|max:64',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        if (! $request->has('attribute_options') || empty($request->input('attribute_options'))) {
            return code_response(10002, '请输入产品选项值');
        }
        $options = json_decode($request->input('attribute_options'));
        $attribute_ids = [];
        try{
            DB::beginTransaction();
            foreach ($options as $attribute_id=>$items) {
                $attribute_ids[] = $attribute_id;
               foreach ($items as $option) {
                   if(ProductAttributeList::where('product_id', $request->input('product_id'))->where('attribute_id', $attribute_id)->where('attribute_value',$option->attribute_value)->exist()) {
                       continue;
                   }
                   $product_attribute_list = new ProductAttributeList();
                   $product_attribute_list->product_id = $request->input('product_id');
                   $product_attribute_list->attribute_id = $attribute_id;
                   $product_attribute_list->attribute_value = $option->attribute_value;
                   $product_attribute_list->attribute_english_value = $option->attribute_english_value;
                   $product_attribute_list->save();
               }
            }
            DB::commit();
            return code_response(10, '添加产品属性值选项成功！', 200, ['data' => Product::attribute_values($request->input('product_id'),$attribute_ids)]);
        }catch (\Exception $e) {
            DB::rollBack();
            return code_response(50001, '添加产品属性值选项失败！');
        }
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
        $product = Product::with('resources', 'attributes','suppliers')->find($id);
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
