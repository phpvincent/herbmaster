<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::where(function ($query) use($request) {
            if ($request->input('site_id')) {
                $query->where('site_id', $request->input('site_id'));
            }
        })->where(function ($query)use($request){
            if ($request->input('keywork')) {
                $query->where('name','like' ,'%'.$request->input('keywork') . '%');
            }
        })->paginate($request->input('limit', 15));
        return code_response(10, '获取标签列表成功！', 200, ['data' => $tags]);
    }
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'name' => ['required', 'string', 'max:32', Rule::unique('tags')->where('site_id', $request->input('site_id'))],
        ]);
        if ($validator->fails()) {
            return code_response(10001, $validator->errors()->first());
        }
        $tag = new Tag();
        $tag->site_id = $request->input('site_id');
        $tag->name = $request->input('name');
        if($tag->save()){
            return code_response(10, '添加标签成功！', 200, ['data' => $tag]);
        }else{
            return code_response(50001, '添加标签失败！');
        }
    }

    public function destory(Request $request)
    {
        $id = $request->input('id');
        if(!$id || !Tag::where('id',$id)->exists()) {
            return code_response(10001,'参数错误！');
        }
        try{
            DB::beginTransaction();
            DB::table('product_tag')->where('tag_id', $id)->delete();
            Tag::destroy($id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return code_response(20213, $e->getMessage());
        }
        return code_response(10, '删除标签成功！');
    }
}
