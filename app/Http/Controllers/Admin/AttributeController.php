<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\ProductAttributeList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::all();
        return code_response(10, '获取属性列表成功！', 200, ['data' => $attributes]);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), ['name' => 'required|unique:attributes,name|max:32','english_name' => 'required|max:32']);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $attribute = new Attribute();
        $attribute->name = $request->input('name');
        $attribute->english_name = $request->input('english_name');
        if($attribute->save()) {
            return code_response(10, '添加属性成功！', 200, ['data' => $attribute]);
        } else {
            return code_response(50001, '添加属性失败！');
        }
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required|exists:attributes,id','english_name' => 'required|max:32']);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $attribute = Attribute::find($request->input('id'));
        $attribute->english_name = $request->input('english_name');
        if($attribute->save()) {
            return code_response(10, '修改属性成功！', 200, ['data' => $attribute]);
        } else {
            return code_response(50001, '修改属性失败！');
        }
    }

    public function destory($id)
    {
        if (!$id || !Attribute::where('id', $id)->exists()) {
            return code_response(10001, '该属性不存在！');
        }
        if (ProductAttributeList::where('attribute_id', $id)->exists()) {
            return code_response(60001, '该属性被使用不允许删除！');
        }
        if(Attribute::destroy($id)) {
            return code_response(10, '删除成功！');
        }else{
            return code_response(50001, '删除失败！');
        }
    }
}
