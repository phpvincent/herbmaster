<?php
if (!function_exists("code_response")) {
    function code_response($code,$msg,$httpcode=200,$data=[])
    {	 
    	  if($code!=10){
    	  	$ip=\Request::getClientIp();
    	  	\Log::error(['code'=>$code,'httpcode'=>$httpcode,'msg'=>$msg,'ip'=>$ip,'path'=>\Request::getRequestUri(),'data'=>$data]);
    	  }
          return response(['code'=>$code,'msg'=>$msg,'data'=>$data],$httpcode);
    }
}