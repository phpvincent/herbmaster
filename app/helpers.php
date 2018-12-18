<?php
if (!function_exists("code_response")) {
    function code_response($code,$msg,$httpcode=200,$data=[])
    {
          return response(['code'=>$code,'msg'=>$msg,'data'=>$data],$httpcode);
    }
}