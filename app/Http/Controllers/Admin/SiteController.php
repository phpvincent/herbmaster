<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Site;
class SiteController extends Controller
{
    public function index(Request $request)
    {   
        $allow_ids=\App\admin::getSetPremiss();
        if(!$allow_ids) return code_response(20800,'not found site that admin can see');
    	$site=Site::whereIn('id',$allow_ids)->orderBy('created_at','desc')->paginate($request->input('limit',15))->toArray();
    	return code_response(10, 'site data find',$site);
    }
    public function update(Request $request)
    {
    		
    }
}
