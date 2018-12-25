<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
                $query->where('name', 'like', '%' .$search. '%')->orWhere('english_name', 'like', '%'.$search.'%');
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
        $validator = Validator::make($request->all(),[
            'site_id' => 'required|exists:sites,id',
            'name' => 'required|string|unique:products,name,'.$request->where('site_id').'|max:64',
            'english_name' => 'required|string|max:64',
            'description' => 'required',
            'price' => 'required|numeric',
            'cost_price' => 'required|numeric',
            'original_price' => 'required|numeric',
            'is_reduce_invenory' => 'required|in:0,1',
            'num' => 'required|integer',
            'is_physical_product' => 'required|in:0,1',
            'weight' => 'required|numeric',
            'type' => 'required|exists:product_type,id',
            'collection_id' => 'required|integer',
            'status' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $product = new Product();
        $product->name = $request->input('name');
        $product->english_name = $request->input('english_name');
        $product->description = $request->input('description');
        $product->price = $request->input('price', 0);
        $product->original_price = $request->input('original_price', 0);
        $product->cost_price = $request->input('cost_price', 0);
        $product->is_reduce_invenory = $request->input('is_reduce_invenory');
        $product->num  = $request->input('num', 0);
        $product->is_physical_product = $request->input('is_physical_product');
        $product->weight = $request->input('weight');
        $product->type = $request->input('type');
        $product->collection_id = $request->input('collection_id');
        $product->admin_id = Auth::guard('admin')->id();
        $product->status = $request->input('status');
        if($product->save()) {
            return code_response(10, '添加产品成功！', 200, ['data' => $product]);
        } else {
            return code_response(50001, '添加产品失败！');
        }



    }
}
