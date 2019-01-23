<?php
use Illuminate\Http\Request;
if (!function_exists("code_response")) {
    function code_response($code,$msg,$httpcode=200,$data=[])
    {	 
    	  if($code!=10){
    	  	\Log::error(['code'=>$code,'httpcode'=>$httpcode,'msg'=>$msg,'path'=>\Request::getRequestUri(),'data'=>$data]);
    	  }
          return response(['code'=>$code,'msg'=>$msg,'data'=>$data],$httpcode);
    }
}
if (!function_exists("check_colum")) {
    function check_colum(Request $request,$array=[])
    {	 
    	$column_miss=[];
    	  foreach($array as $k => $v){
    	  	if(!$request->has($v)) $column_miss[]=$v;
    	  }
          if($column_miss==null){
          	return null;
          }
          return $column_miss;
    }
}