<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\Collection_product;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeList;
use App\Models\ProductType;
use App\Models\Resource;
use App\Models\Supplier;
use Carbon\Carbon;
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
        $order = $request->input('order', 'id');
        $by = $request->input('by', 'asc');
        $products = Product::where('site_id', $site_id)->where(function ($query) use ($search) {
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
        })->orderBy($order, $by)->paginate($per_page);
        return code_response(10, '获取产品列表成功！', 200, ['data' => $products]);
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
            if ($product_attributes) {
                $check_sku = $this->check_sku_unique($product_attributes);
                if ($check_sku !== true) {
                    return code_response(10004, 'sku码不能重复！');
                }
            }

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
            $product->sku = $request->input('sku', '');
            $product->bar_code = $request->input('bar_code', '');
            if ($product->save()) {
                //添加属性信息
                if ($attribute_options) {
                    $product_attribute_list = [];
                    foreach ($attribute_options as $options) {
                        foreach ($options->values as $value) {
                            if (ProductAttributeList::where('product_id', $product->id)->where('attribute_id', $options->attribute)->where('attribute_value', $value->attribute_value)->exists()) {
                                continue;
                            }
                            $option = new ProductAttributeList();
                            $option->attribute_id = $options->attribute;
                            $option->product_id = $product->id;
                            $option->attribute_value = $value->attribute_value;
                            $option->attribute_english_value = $value->attribute_english_value;
                            $option->save();
                            $product_attribute_list[$options->attribute . '_' . $value->attribute_value] = $option->id;
                        }
                    }
                }
                //添加产品变种
                if ($product_attributes) {
                    foreach ($product_attributes as $attribute) {
                        $product_attribute = new ProductAttribute();
                        $product_attribute->product_id = $product->id;
                        $product_attribute->attribute_list_ids = $this->get_product_attribute_ids($attribute->attributes, $product_attribute_list);
                        $product_attribute->price = isset($attribute->price) ? $attribute->price : $product->price;
                        $product_attribute->sku = $this->set_sku($product, $attribute->sku);
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
                    $supplier->contact = $request->input('supplier_contact', '') == null ? '' : $request->input('supplier_contact');
                    $supplier->phone = $request->input('supplier_phone', '') == null ? '' : $request->input('supplier_phone');
                    $supplier->price = $request->input('supplier_price', 0);
                    $supplier->num = $request->input('supplier_num', 0);
                    $supplier->remark = $request->input('supplier_remark', '') == null ? '' : $request->input('supplier_remark');
                    $supplier->save();
                }

                //添加标签
                if ($request->has('product_tags') && !empty($request->input('product_tags'))) {
                    $product_tags = json_decode($request->input('product_tags'));
                    if ($product_tags) {
                        foreach ($product_tags as $product_tag) {
                            DB::table('product_tag')->insert(['product_id' => $product->id, 'tag_id' => $product_tag]);
                        }
                    }
                }
                //添加集合
                if ($request->has('product_collections') && !empty($request->input('product_collections'))) {
                    $product_collections = json_decode($request->input('product_collections'));
                    if ($product_collections) {
                        foreach ($product_collections as $product_collection) {
                            DB::table('collections_products')->insert([
                                'products_id' => $product->id,
                                'collections_id' => $product_collection->collection_id,
                                'remark' => $product_collection->remark,
                                'sort' => $product_collection->sort,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                    }
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
//            'site_id' => 'required|exists:sites,id',
            'id' => 'required|integer|exists:products,id',
            'name' => ['required', 'string', 'max:64', Rule::unique('products')->where('site_id', $request->input('site_id'))->where('id', '<>', $request->input('id'))],
            'english_name' => 'required|string|max:64',
            'description' => 'required:string',
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
        $product->sku = $request->input('sku', '');
        $product->bar_code = $request->input('bar_code', '');
        if ($product->save()) {
            return code_response(10, '修改产品成功！', 200, ['data' => $product]);
        } else {
            return code_response(50001, '修改产品失败！');
        }
    }

    public function edit_resources(Request $request)
    {
        $product_id = $request->input('id');
        $product_resources = json_decode($request->input('product_resources'));
        if ($product_resources) {
            try {
                DB::beginTransaction();
                foreach ($product_resources as $resource) {
                    if (DB::table('product_resource')->where('product_id', $product_id)->where('resource_id', $resource->resource_id)->exists()) {
                        DB::table('product_resource')->where('product_id', $product_id)->where('resource_id', $resource->resource_id)->update(['is_index' => $resource->is_index, 'sort' => $resource->sort]);
                    } else {
                        DB::table('product_resource')->insert(['product_id' => $product_id, 'resource_id' => $resource->resource_id, 'is_index' => $resource->is_index, 'sort' => $resource->sort]);
                    }
                }
                DB::commit();
                return code_response(10, '修改产品资源成功！');
            } catch (\Exception $e) {
                DB::rollBack();
                return code_response(50001, '修改产品资源失败！');
            }
        }
        return code_response(10, '修改产品资源成功！');
    }

    public function del_resources(Request $request)
    {
        $product_id = $request->input('id');
        $product_resources = json_decode($request->input('product_resource_ids'));
        if ($product_resources) {
            try {
                DB::beginTransaction();
                foreach ($product_resources as $resource) {
                    DB::table('product_resource')->where('product_id', $product_id)->where('resource_id', $resource)->delete();
                }
                DB::commit();
                return code_response(10, '删除产品资源成功！');
            } catch (\Exception $e) {
                DB::rollBack();
                return code_response(50001, '删除产品资源失败！');
            }
        }
        return code_response(10, '删除产品资源成功！');
    }

    public function add_variant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'price' => 'required|numeric',
            'sku' => 'required|string|max:32',
            'bar_code' => 'required|string|max:32',
            'num' => 'required|integer',
            'attribute_list' => 'required'
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $sku = $this->set_sku(Product::find($request->input('product_id')), $request->input('sku'));
        if (ProductAttribute::where('product_id', $request->input('product_id'))->where('sku', $sku)->exists()) {
            return code_response(10002, 'sku必须唯一！');
        }
//        if (! $request->has('attribute_list')) {
//            return code_response(10002, '请输入产品属性选项及值！');
//        }
        $attribute_list = json_decode($request->input('attribute_list'));
        $ids = [];
        foreach ($attribute_list as $key => $value) {
            $id = ProductAttributeList::where('product_id', $request->input('product_id'))->where('attribute_id', $key)->where('attribute_value', $value->attribute_value)->value('id');
            if (!$id) {
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
        $product_attribute->sku = $sku;
        $product_attribute->bar_code = $request->input('bar_code');
        $product_attribute->num = $request->input('num');
        if ($product_attribute->save()) {
            return code_response(10, '添加产品变种成功！', 200, ['data' => ['variant' => $product_attribute, 'attribute_options' => Product::attribute_values($request->input('product_id'))]]);
        } else {
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
            'attribute_list' => 'required'
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $sku = $request->input('sku');
        if (ProductAttribute::where('product_id', $request->input('product_id'))->where('sku', $sku)->exists()) {
            return code_response(10002, 'sku必须唯一！');
        }
//        if (! $request->has('attribute_list')) {
//            return code_response(10002, '请输入产品属性选项及值！');
//        }
        $attribute_list = json_decode($request->input('attribute_list'));
        $ids = [];
        $product_attribute = ProductAttribute::find($request->input('id'));
        foreach ($attribute_list as $key => $value) {
            $id = ProductAttributeList::where('product_id', $product_attribute->product_id)->where('attribute_id', $key)->where('attribute_value', $value->attribute_value)->value('id');
            if (!$id) {
                $product_attribute_list = new ProductAttributeList();
                $product_attribute_list->product_id = $product_attribute->product_id;
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
        $product_attribute->attribute_list_ids = implode(',', $ids);
        $product_attribute->price = $request->input('price');
        $product_attribute->sku = $sku;
        $product_attribute->bar_code = $request->input('bar_code');
        $product_attribute->num = $request->input('num');
        if ($product_attribute->save()) {
            return code_response(10, '修改产品变种成功！', 200, ['data' => ['variant' => $product_attribute, 'attribute_options' => Product::attribute_values($product_attribute->product_id)]]);
        } else {
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
        if (!$request->has('attribute_options') || empty($request->input('attribute_options'))) {
            return code_response(10002, '请输入产品选项值');
        }
        $options = json_decode($request->input('attribute_options'));
        $attribute_ids = [];
        try {
            DB::beginTransaction();
            foreach ($options as $items) {
                $attribute_ids[] = $items->attribute;
                foreach ($items->values as $option) {
                    if (ProductAttributeList::where('product_id', $request->input('product_id'))->where('attribute_id', $items->attribute)->where('attribute_value', $option->attribute_value)->exists()) {
                        continue;
                    }
                    $product_attribute_list = new ProductAttributeList();
                    $product_attribute_list->product_id = $request->input('product_id');
                    $product_attribute_list->attribute_id = $items->attribute;
                    $product_attribute_list->attribute_value = $option->attribute_value;
                    $product_attribute_list->attribute_english_value = $option->attribute_english_value;
                    $product_attribute_list->save();
                }
            }
            DB::commit();
            return code_response(10, '添加产品属性值选项成功！', 200, ['data' => Product::attribute_values($request->input('product_id'), $attribute_ids)]);
        } catch (\Exception $e) {
            DB::rollBack();
            return code_response(50001, '添加产品属性值选项失败！');
        }
    }

    public function destory(Request $request)
    {
        $id = $request->input('id', 0);
        if (!$id || !Product::where('id', $id)->exists()) {
            return code_response(10001, '该产品不存在失败！');
        }
        if (Product::destroy($id)) {
            return code_response(10, '删除产品成功！');
        } else {
            return code_response(50001, '删除产品失败！');
        }
    }

    public function info(Request $request)
    {
        $id = $request->input('id', 0);
        $product = Product::with('resources', 'attributes', 'suppliers', 'collections', 'tags')->find($id);
        $product->type_name = ProductType::where('id', $product->type)->value('name');
        $product->attribute_options = Product::attribute_values($id);
        $product->product_attributes = Product::product_attribute_list($id);
        return code_response(10, '获取产品信息成功！', 200, ['data' => $product]);
    }

    public function edit_tags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $product_tags = json_decode($request->input('product_tags'));
        if ($product_tags){
            try {
                DB::beginTransaction();
                foreach ($product_tags as $product_tag) {
                    if (!DB::table('product_tag')->where('product_id', $request->input('product_id'))->where('tag_id', $product_tag)->exists()) {
                        DB::table('product_tag')->insert(['product_id' => $request->input('product_id'), 'tag_id' => $product_tag]);
                    }
                }
                DB::commit();
                return code_response(10, '修改产品标签成功！');
            } catch (\Exception $e) {
                DB::rollBack();
                return code_response(50001, '修改产品标签失败！');
            }
        }
        return code_response(10, '修改产品标签成功！');
    }

    public function delete_tags(Request $request)
    {
        $tag_ids = json_decode($request->input('tag_ids'));
        $id = $request->input('id');
        if ($tag_ids) {
            try {
                DB::beginTransaction();
                foreach ($tag_ids as $tag_id) {
                    DB::table('product_tag')->where('product_id', $id)->where('tag_id', $tag_id)->delete();
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return code_response(20213, $e->getMessage());
            }
        }
        return code_response(10, '删除产品标签成功！');
    }

    public function get_products_by_conditions(Request $request)
    {
        $site_id = $request->input('site_id', 0);
        $pre_page = $request->input('pre_page', 15);
        $page = $request->input('page', 1);
        $conditions = json_decode($request->input('conditions'), true);
        $products = DB::table('products as p')->join('product_types as pt', 'p.type', '=', 'pt.id', 'left')
            ->join('suppliers as s', 's.product_id', '=', 'p.id', 'left')
            ->join('product_tag as pg', 'p.id', '=', 'pg.product_id', 'left')
            ->join('tags as t', 't.id', '=', 'pg.tag_id', 'left')->where('p.site_id', $site_id);
        if ($conditions) {
            $match = $conditions['match'];
            $in_values = [];
            $not_in_values = [];
            foreach ($conditions['conditions'] as $key => $condition) {
                if ($condition['key'] == 'tag') {
                    if ($condition['term'] == '()') {
                        $in_values[] = $condition['value'];
                    } elseif ($condition['term'] == ')(') {
                        $not_in_values[] = $condition['value'];
                    }
                    unset($conditions['conditions'][$key]);
                }
            }
            if ($in_values) {
                $conditions['conditions'][] = ['key' => 'tag', 'term' => '()', 'value' => $in_values];
            }
            if ($not_in_values) {
                $conditions['conditions'][] = ['key' => 'tag', 'term' => ')(', 'value' => $not_in_values];
            }
            $products->where(function ($query) use ($conditions, $match) {
                foreach ($conditions['conditions'] as $condition) {
                    if ($condition['key'] == 'type') {
                        $key = 'pt.name';
                    } elseif ($condition['key'] == 'supplier') {
                        $key = 's.url';
                    } elseif ($condition['key'] == 'tag') {
                        $key = 't.name';
                    } else {
                        $key = 'p.' . $condition['key'];
                    }
                    if ($match == 'all') {
                        if ($condition['term'] == '..%') {
                            $query->where($key, 'like', $condition['value'] . '%');
                        } elseif ($condition['term'] == '%..') {
                            $query->where($key, 'like', '%' . $condition['value']);
                        } elseif ($condition['term'] == '()') {
                            $query->whereIn($key, $condition['value']);
                        } elseif ($condition['term'] == ')(') {
                            $query->whereNotIn($key, $condition['value']);
                        } else {
                            $query->where($key, $condition['term'], $condition['value']);
                        }
                    } else {
                        if ($condition['term'] == '..%') {
                            $query->orWhere($key, 'like', $condition['value'] . '%');
                        } elseif ($condition['term'] == '%..') {
                            $query->orWhere($key, 'like', '%' . $condition['value']);
                        } elseif ($condition['term'] == '()') {
                            $value = $condition['value'];
                            $query->orWhere(function ($query) use ($key, $value) {
                                $query->whereIn($key, $value);
                            });
                        } elseif ($condition['term'] == ')(') {
                            $value = $condition['value'];
                            $query->orWhere(function ($query) use ($key, $value) {
                                $query->whereNotIn($key, $value);
                            });
                        } else {
                            $query->orWhere($key, $condition['term'], $condition['value']);
                        }
                    }
                }
            });
        }
        $products = $products->select('p.id', 'p.name')->groupBy('p.id');
        if($request->input('is_paginate', 0)){
            $products->paginate($pre_page);
        }else{
            $products->get();
        }
        if ($products) {
            foreach ($products as $product) {
                $product->image = Product::with('index_thumb')->find($product->id);
            }
        }
        return code_response(10, '获取成功！', 200, ['data' => $products]);
    }

    private function check_attribute_values($attribute_values)
    {
        foreach ($attribute_values as $options) {
            if (!Attribute::where('id', $options->attribute)->exists()) {
                return ['code' => 0, 'msg' => '该属性不存在！'];
            }
            foreach ($options->values as $value) {
                if (!isset($value->attribute_value) || !isset($value->attribute_english_value)) {
                    return ['code' => 0, 'msg' => '属性值及英文属性值均不能为空!'];
                }
            }
        }
        return true;
    }

    private function check_sku_unique($attributes)
    {
        $skus = array_column($attributes, 'sku');
        if (in_array('', $skus)) {
            return false;
        }
        if (count(array_unique($skus)) < count($skus)) {
            return false;
        }

        return true;
    }

    private function set_sku($product, $sku)
    {
        return $product->type . '-' . $product->id . '-' . $sku;
    }

    private function get_product_attribute_ids($attributes, $product_attribute_list)
    {
        $ids = [];
        foreach ($attributes as $key => $value) {
            if (isset($product_attribute_list[$value->key . '_' . $value->value])) {
                $ids[] = $product_attribute_list[$value->key . '_' . $value->value];
            }
        }
        return implode(',', $ids);
    }
}
