<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\CollectionProduct;
use App\Models\Resource;
use Carbon\Carbon;
use \DB;
use \Exception;
use App\Http\Controllers\Controller;
class CollectionController extends Controller
{
    public function index(Request $request)
    {
    	$data=Collection::where(function($query)use($request){
   			if($request->has('status')&&is_numeric($request->input('instype'))){
   				$query->where('status',$request->input('cate_id'));
   			}
   			if($request->has('site_id')&&is_numeric($request->input('instype'))){
   				$query->where('site_id',$request->input('site_id'));
   			}
            if($request->has('name')){
                $query->where('name','like',"%".$request->input('name')."%");
            }

            if($request->has('instype')&&$request->input('instype')!=null&&is_numeric($request->input('instype'))){
                $query->where('instype',$request->input('instype'));
            }
   		})
   		//->where('start_time','<',Carbon::now()->toDateTimeString())
   		->paginate($request->input('limit',15))->toArray();
    	return code_response(10, 'get Collections list success',200,$data);
    }
    public function collections_products (Request $request)
    {	
    	if(!$request->has('collections_id')||(int)$request->input('collections_id')!=$request->input('collections_id'))     		return code_response(20201, 'collections_id not allowed');
    	$collection=Collection::find($request->input('collections_id'));
    	if($collection==null) return code_response(20202, 'collection not find');
        if($request->input('is_admin',0)==0){
            if($collection->status!=0||strtotime($collection->start_time)>time()){
                return code_response(20203, 'collection is not using');
            }
        }
        //获取排序规则
        if($request->has('sort_type')){
                $asc=substr($request->input('sort_type','default_desc'),strripos($request->input('sort_type','default_desc'),"_")+1);
                $sort_art=substr($request->input('sort_type','default_desc'),0,strrpos($request->input('sort_type','default_desc'),"_"));
                switch ($request->input('sort_type')) {
                    case 'time':
                      $sort_art='products.created_at';
                        break;
                    case 'price':
                      $sort_art='products.price';
                        break;
                    case 'num':
                      $sort_art='products.num';
                        break;
                    case 'sort':
                      $sort_art='collections_products.sort';
                        break;
                    default:
                      $sort_art='collections_products.sort';
                        break;
                }
            }else{
                $sort_type=$collection->get_sort_type()['sort_type'];
                $asc=$collection->get_sort_type()['asc'];
                $sort_art='collections_products.sort';
                $asc='desc';
            }
    	$data=CollectionProduct::
        select('collections_products.*','products.created_at','products.price','products.num')
        ->leftjoin('products','collections_products.products_id','=','products.id')
        ->where(function($query)use($request){
    		$query->where('collections_id',$request->input('collections_id'));
    	})
        ->orderBy($sort_art,$asc)
    	->paginate($request->input('limit',15))->toArray();
    	return code_response(10, 'get Collections product list success',200,$data);
    }
    public function set_collection_status (Request $request)
    {
    	if(!$request->has('collections_id')||(int)$request->input('collections_id')!=$request->input('collections_id'))     		return code_response(20204, 'collections_id not allowed');
    	if(!$request->has('status')||(int)$request->input('status')!=$request->input('status'))     		return code_response(20205, 'status code not allowed');
    	$msg=Collection::where('id',$request->input('collections_id'))->update(['status'=>$request->input('status')]);
    	if($msg==false)	return code_response(20206, 'status change failed');
    	return code_response(10, 'status change success');
    }
    public function set_collection_start_time(Request $request)
    {
    	if(!$request->has('collections_id')||(int)$request->input('collections_id')!=$request->input('collections_id'))     		return code_response(20207, 'collections_id not allowed');
    	if(!$request->has('start_time')||strtotime($request->input('start_time'))<=time())   return code_response(20208, 'start_time value not allowed');
    	$msg=Collection::where('id',$request->input('collections_id'))->update(['start_time'=>$request->input('start_time')]);
    	if($msg==false)	return code_response(20209, 'start_time change failed');
    	return code_response(10, 'start_time change success');
    }
    public function update(Request $request)
    {
    	if(!$request->has('collections_id')||(int)$request->input('collections_id')!=$request->input('collections_id'))     		return code_response(20210, 'collections_id not allowed');
    	$Collection=Collection::where('id',$request->input('collections_id'))->first();
    	$msg=$Collection->update($request->only($Collection->fillable));
    	if($msg==false) return code_response(20211, 'collections update failed');
    	return code_response(10, 'collections update success');
    }
    public function destory(Request $request)
    {
    	if(!$request->has('collections_id')||(int)$request->input('collections_id')!=$request->input('collections_id'))     		return code_response(20212, 'collections_id not allowed');  
    	try{
    		DB::beginTransaction();
    		$msg=Collection::where('id',$request->input('collections_id'))->delete();  
    		if($msg==false) throw new Exception("collection data delete failed");
    		$msg1=CollectionProduct::where('collections_id',$request->input('collections_id'))->delete();
    		if($msg==false) throw new Exception("collection data delete failed");
    		DB::commit();
    	} catch (\Exception $e) {
            DB::rollBack();
            return code_response(20213, $e->getMessage());
    	}
    	return code_response(10, 'collection data delete success');
    }
    public function upload(Request $request)
    {
    	if(!$request->has('name')||!$request->has('status')||!$request->has('site_id')||!$request->has('img')||!$request->has('ids')) 	return code_response(20214, 'request data missing');  
    	if(Resource::find($request->input('img'))==null) return code_response(20215, 'img_id not found');  
    	if($request->has('start_time')&&strtotime($request->input('start_time'))<time()-10)	return code_response(20216, 'start_time not allowed');
    	if(!$request->has('start_time')) request()->offsetSet('start_time',Carbon::now()->toDateTimeString());
    	if((int)$request->input('status')!=$request->input('status')) return code_response(20217, 'status code not allowed');
    	try{
    		DB::beginTransaction();
    		$collections_id=Collection::insertGetId($request->except('ids'));
    		if($collections_id==false) throw new Exception("collection data insert failed");
    		$ids=json_decode($request->input('ids'),true);
    		foreach($ids as $k => $v){
    			if(CollectionProduct::where([['collections_id',$collections_id],['products_id',$v['products_id']]])->first()!=null) continue;
    			$msg=CollectionProduct::insert(['collections_id'=>$collections_id,'products_id'=>$v['products_id'],'remark'=>isset($v['remark'])?$v['remark']:null,'sort'=>isset($v['sort'])?$v['sort']:0]);
    			if($msg==false) throw new Exception("CollectionProducts data insert failed");
    		}   
    		DB::commit();		
    	}catch(\Exception $e) {
    		DB::rollBack();
    		 return code_response(20218, $e->getMessage());
    	}
    	
    	return code_response(10, 'collection data insert success');
    }
    public function add_products(Request $request)
    {
    	if(!$request->has('collections_id')||!$request->has('products_ids')||(int)$request->input('collections_id')!=$request->input('collections_id')) 	return code_response(20219, 'request data missing');  
    	$collections_id=$request->input('collections_id');
    	if(Collection::find($collections_id)==null) return code_response(20220, 'collections data not find');  
    	//$ids=json_decode($request->input('products_ids'),true);
    	try{
    		DB::beginTransaction();
    		$ids=json_decode($request->input('products_ids'),true);
    		foreach($ids as $k => $v){
    			if(CollectionProduct::where([['collections_id',$collections_id],['products_id',$v['products_id']]])->first()!=null) continue;
    			$msg=CollectionProduct::insert(['collections_id'=>$collections_id,'products_id'=>$v['products_id'],'remark'=>isset($v['remark'])?$v['remark']:null,'sort'=>isset($v['sort'])?$v['sort']:0]);
    			if($msg==false) throw new Exception("CollectionProducts data insert failed");
    		}   
    		DB::commit();		
    	}catch(\Exception $e) {
    		DB::rollBack();
    		 return code_response(20221, $e->getMessage());
    	}
    	return code_response(10, 'collection data insert success');
    }
    public function del_products(Request $request)
    {
        if(!$request->has('ids')) return code_response(20222, 'ids code not get');
        if(!$request->has('collections_id')) return code_response(20223, 'collections_id code not get');
        $ids=json_decode($request->input('ids'),true);
        try{
            DB::beginTransaction();
            foreach($ids as $k => $v){
                $msg=CollectionProduct::where([['collections_id',$request->input('collections_id')],['products_id',$v]])->delete();
                if($msg==false) throw new Exception("products delete failed");
            }
            DB::commit();
        }catch(\Exception $e) {
            DB::rollBack();
            return code_response(20224, $e->getMessage());
        }
        return code_response(10, 'products_id delete success',200,$ids);
    }
}

