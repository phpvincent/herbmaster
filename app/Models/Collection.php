<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = 'collections';
    protected $primaryKey = 'id';
   	public $timestamps = true;
   	public $fillable = ['status','name','site_id','description','img','template_type','remark','start_time'];
}
