<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Validator;

class SupplierController extends Controller
{
    public function edit(Request $request)
    {
        if(!$request->input('supplier_id') || !Supplier::where('id', $request->input('supplier_id'))->exists()) {
            return code_response(10001, '供货商不存在！');
        }
        $supplier = Supplier::find($request->input('supplier_id'));
        $supplier->url = $request->input('supplier_url');
        $supplier->contact = $request->input('supplier_contact', '') == null ? '' : $request->input('supplier_contact');
        $supplier->phone = $request->input('supplier_phone', '')  == null ? '' : $request->input('supplier_phone', '');
        $supplier->price = floatval($request->input('supplier_price', 0));
        $supplier->num = intval($request->input('supplier_num', 0));
        $supplier->remark = $request->input('supplier_remark', '')  == null ? '' : $request->input('supplier_remark', '');
        if($supplier->save()){
            return code_response(10, '修改供货商信息成功！', 200, ['data'=>$supplier]);
        }else{
            return code_response(50001, '修改供货商信息失败！');
        }
    }

    public function destory(Request $request)
    {
        if(!$request->input('id') || !Supplier::where('id', $request->input('id'))->exists()) {
            return code_response(10001, '供货商不存在！');
        }
        if(Supplier::where('id', $request->input('id'))->delete()) {
            return code_response(10, '删除供货商信息成功！');
        }else{
            return code_response(50001, '删除供货商信息失败！');
        }
    }
}
