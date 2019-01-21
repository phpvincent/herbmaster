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
    	$site_id=$request->input('site_id',null);
        if($site_id==null||(int)$site_id!=$site_id) return code_response(20801, 'site_id not allowed');
        $site=Site::::where('id',$request->input('collections_id'))->first();
        if($site==null) return code_response(20802, 'site data not found');
        $msg=$Collection->update($request->only($Collection->fillable));
        if($msg==false) return code_response(20803, 'site update failed');
    }
    public function get_site_info(Request $request)
    {
        $site_id=$request->input('site_id',null);
        if($site_id==null||(int)$site_id!=$site_id) return code_response(20804, 'site_id not allowed');
        $site=Site::::where('id',$request->input('collections_id'))->first();
        if($site==null) return code_response(20805, 'site data not found');
    }
}
