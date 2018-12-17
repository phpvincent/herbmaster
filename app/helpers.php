<?php
if (!function_exists("code_response")) {
    function code_response($code,$msg,$httpcode=200)
    {
          return response(['code'=>$code,'msg'=>$msg],$httpcode);
    }
}