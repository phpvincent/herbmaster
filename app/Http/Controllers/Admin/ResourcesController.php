<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Resource;
use Illuminate\Support\Facades\Auth;
class ResourcesController extends Controller
{
    //资源类控制器
    /**
     * [upload description]
     * @param  Request $request [description]
     * @return [type:post]           [文件上传]
     */
    public function upload(Request $request)
    {
    	if(!$request->has('cate_id')||$request->input('cate_id')==null){
    		$cate_id=0;
    	}
    	$file = $request->file('file');
    	if(!$file) return code_response(20100, 'file not found');
    	$type=$file->getClientOriginalExtension();
    	if(!in_array($type, Resource::get_allow_filetype())) return code_response(20101, 'Filetype not allowed');
    	$size=Resource::get_size($file->getClientSize());
    	$admin_id = Auth::guard('admin')->payload()['sub'];
    	if(!$request->has('site_id')) return code_response(20102, 'site_id not find ,please check your commit');
    	$site_id=$request->input('site_id');
    	$filename=$file->getClientOriginalName();
    	//dd(Storage::disk('resources')->directories());
    	/*$dir=storage_path().'\\app\\public\\resources\\'.$type.'\\'.date('Y-m-d');
    	if (!is_dir($dir)){
                mkdir($dir);
            }
    	$newname=$dir.'\\'.$filename;*/
    	$newname=$type.'/'.date('YmdHis').$filename;
    	$bool=Storage::disk('resources')->put($newname,file_get_contents($file->getRealPath()));
    	if(!$bool) return code_response(20103, 'file upload faild');
    	$resources=new Resource;
    	$resources->type=$type;
    	$resources->size=$size;
    	$resources->admin_id=$admin_id;
    	$resources->cate_id=$cate_id;
    	$resources->site_id=$site_id;
    	$resources->path=Storage::disk('resources')->url($newname);
    	$bool=$resources->save();
    	if(!$bool) return code_response(20104, 'file upload faild');
    	return code_response(10, 'file upload success',200,$resources->toArray());
    }
    /**
     * [get_file_list description]
     * @param  Request $request [description]
     * @return [type:get]           [资源列表展示]
     */
   public function get_file_list(Request $request)
   {
    	//if(!$request->has('site_id')) return code_response(20105, 'site_id not find ,please check your commit');
   		$cate_id=$request->input('cate_id',0);
   		$limit=$request->input('limit',0);
   		$data=Resource::where(function($query)use($request){
   			if($request->has('cate_id')){
   				$query->where('cate_id',$request->input('cate_id'));
   			}
   			if($request->has('site_id')){
   				$query->where('site_id',$request->input('site_id'));
   			}
   		})
   		->paginate(3)->toArray();
    	return code_response(10, 'fet file list success',200,$data);
   }
   public function get_file_by_id(Request $request)
   {	
    	if(!$request->has('id')) return code_response(20105, 'file id not find ,please check your commit');
    	 if($data=Resource::find($request->input('id'))){
    		return code_response(10, 'file slect success',200,$data->toArray());
    	 }else{
    		return code_response(20106, 'file id wrong',200);
    	 }
   }
   public function get_filepath_by_id(Request $request)
   {
    	if(!$request->has('id')) return code_response(20107, 'file id not find ,please check your commit');
    	 if($data=Resource::find($request->input('id'))->path){
    		return code_response(10, 'file slect success',200,['path'=>$data]);
    	 }else{
    		return code_response(20108, 'file id wrong',200);
    	 }
   }
}
