<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\resources;
class ResourcesController extends Controller
{
    //资源类控制器
    /**
     * [upload description]
     * @param  Request $request [description]
     * @return [type:post]           [description]
     */
    public function upload(Request $request)
    {

    	if(!$request->has('cate_id')||$request->input('cate_id')==null){
    		$cate_id=0;
    	}
    	$type='';
    }
}
