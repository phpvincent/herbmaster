<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = 'collections';
    protected $primaryKey = 'id';
   	public $timestamps = true;
   	public $fillable = ['status','name','site_id','description','img','template_type','remark','start_time','sort_type'];
   	public function get_sort_type(){
   		switch ($this->sort_type) {
   			case '0':
   				$sort_type='collections_products.sort';
   				$asc='asc';
   				break;
			case '1':
   				$sort_type='collections_products.sort';
   				$asc='desc';
   				break;
			case '2':
   				$sort_type='products.created_at';
   				$asc='asc';
   				break;
			case '3':
   				$sort_type='products.created_at';
   				$asc='desc';
   				break;
			case '4':
   				$sort_type='products.price';
   				$asc='asc';
   				break;
			case '5':
   				$sort_type='products.price';
   				$asc='desc';
   				break;
			case '6':
   				$sort_type='products.num';
   				$asc='asc';
   				break;
			case '7':
   				$sort_type='products.num';
   				$asc='desc';
   				break;
   			
   			default:
   				$sort_type='collections_products.sort';
   				$asc='asc';
   				break;
   		}
   		return ['sort_type'=>$sort_type,'asc'=>$asc];
   	}
}
