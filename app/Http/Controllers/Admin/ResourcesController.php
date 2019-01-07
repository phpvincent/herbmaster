<?php

namespace App\Http\Controllers\Admin;
use Intervention\Image\ImageManager as image;
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
      $dir_arr=$this->getDir();
      if(!in_array($type, $dir_arr))  mkdir(storage_path('app/public/resources').'/'.$type);
    	$size=Resource::get_size($file->getClientSize());
      if($size > 8*1024*1024) return code_response(20102, 'Size not allowed');
    	$admin_id = Auth::guard('admin')->payload()['sub'];
    	if(!$request->has('site_id')) return code_response(20103, 'site_id not find ,please check your commit');
    	$site_id=$request->input('site_id');
    	$filename=$file->getClientOriginalName();
    	$timestring=date('YmdHis');
    	//dd(Storage::disk('resources')->directories());
    	/*$dir=storage_path().'\\app\\public\\resources\\'.$type.'\\'.date('Y-m-d');
    	if (!is_dir($dir)){
                mkdir($dir);
            }
    	$newname=$dir.'\\'.$filename;*/
    	$newname=$type.'/'.$timestring.$filename;
      if(in_array($type,Resource::get_img_type())){
      	$manager = new Image(array('driver' => 'GD'));
        $bool=$manager->make($file)->insert(asset('storage/water.png'),'top-left', 15, 10)->save(storage_path('app/public/resources').'/'.$newname);
      	//$bool=Storage::disk('resources')->put($newname,file_get_contents($file->getRealPath()));
      	if(!$bool) return code_response(20104, 'file upload faild');

        try{
            //制作缩略图
            $thum_name=$type.'/'.'thum-'.$timestring.$filename;
            $thum_path=storage_path('app/public/resources').'/'.$thum_name;
            $manager = new Image(array('driver' => 'GD'));
            $width=$request->input('width',300);
            $height=$request->input('height',200);
            $image = $manager->make(storage_path('app/public/resources').'/'.$newname)->resize($width,$height)->save($thum_path);
            //$thum_path=Storage::disk('resources')->url($thum_name);
            $thum_path=asset("storage/resources/".$thum_name);
          }catch(\Exception $e){
            return code_response(20105, 'thum_img make faild');
          }
      }else{
          $bool=Storage::disk('resources')->put($newname,file_get_contents($file->getRealPath()));
          if(!$bool) return code_response(20104, 'file upload faild');
          $thum_path=asset('storage/file.jpg');
      }  
    	$resources=new Resource;
    	$resources->type=$type;
    	$resources->size=$size;
    	$resources->admin_id=$admin_id;
    	$resources->cate_id=$cate_id;
    	$resources->site_id=$site_id;
      $resources->name=$filename;
    	//$resources->path=Storage::disk('resources')->url($newname);
    	$resources->path=asset('storage/resources/'.$newname);
    	$resources->thum_path=$thum_path;
    	$bool=$resources->save();
    	if(!$bool) return code_response(20106, 'file upload faild');
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
   		//$cate_id=$request->input('cate_id',0);
   		$data=Resource::where(function($query)use($request){
   			if($request->has('cate_id')){
   				$query->where('cate_id',$request->input('cate_id'));
   			}
   			if($request->has('site_id')){
   				$query->where('site_id',$request->input('site_id'));
   			}
        if($request->has('name')){
          $query->where('name','like',"%".$request->input('name')."%");
        }
   		})
      ->orderBy('created_at','desc')
   		->paginate($request->input('limit',15))->toArray();
    	return code_response(10, 'get file list success',200,$data);
   }
   public function get_file_by_id(Request $request)
   {	
    	if(!$request->has('id')) return code_response(20107, 'file id not find ,please check your commit');
    	 if($data=Resource::find($request->input('id'))){
    		return code_response(10, 'file slect success',200,$data->toArray());
    	 }else{
    		return code_response(20108, 'file id wrong',200);
    	}
   }
   public function get_filepath_by_id(Request $request)
   {code_response(10, 'file slect success',200);
    	if(!$request->has('id')) return code_response(20109, 'file id not find ,please check your commit');
    	 if($data=Resource::find($request->input('id'))){
    	 	//如果包含此字段，返回缩略图路径
    	  return code_response(10, 'file select success',200,['path'=>$data->path,'thum_path'=>$data->thum_path]);
    	 }else{
    		return code_response(20110, 'file id wrong',200);
    	 }
   }
   public function del_file_by_id(Request $request)
   {
      if(!$request->has('id')) return code_response(20111, 'file id not find ,please check your commit');
      if($data=Resource::find($request->input('id'))){
        //如果包含此字段，返回缩略图路径
          $bool=$data->delete();
          if(!$bool)           return code_response(20112, 'dbservice delete file wrong ,please check your commit');
          $this->del_resources_file($data->path);
          $this->del_resources_file($data->thum_path);
        return code_response(10, 'file delete success',200,['id'=>$data->id]);
       }else{
        return code_response(20113, 'file id wrong',200);
       }
   } 
   public function del_file_by_ids(Request $request)
   {
      if(!$request->has('ids')) return code_response(20114, 'file id not find ,please check your commit');
      $wrong_id=[];
      $right_id=[];
      $ids=json_decode($request->input('ids'),true);
      foreach($ids as $k => $v){
          if($data=Resource::find($v)){
              //如果包含此字段，返回缩略图路径
                $bool=$data->delete();
                if(!$bool){
                            $wrong_id[]=$v;
                            continue;
                } 
                $this->del_resources_file($data->path);
                $this->del_resources_file($data->thum_path);
                $right_id[]=$v;
             }else{
              $wrong_id[]=$v;
             }
         }
      return code_response(10, 'files delete finish',200,['success_ids'=>$right_id,'fail_ids'=>$wrong_id]);
    }
    /**
     * [del_resources_file description]
     * @param  string $file_path file_path
     * @return boolean           false or true
     */
    public function del_resources_file($file_path)
    {
       $delstr=asset('storage/resources').'/';
       $length=strlen($delstr); 
       $count=strpos($file_path,$delstr);
       $del_path=substr_replace($file_path,'',$count,$length);
       if(!Storage::disk('resources')->delete($del_path)) return false;
       return true;
    }
    public function getDir() {
    $dir=storage_path('app/public/resources');
    $dirArray[]=NULL;
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            //去掉"“.”、“..”以及带“.xxx”后缀的文件
            if ($file != "." && $file != ".."&&!strpos($file,".")) {
                $dirArray[$i]=$file;
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $dirArray;
    }
}
