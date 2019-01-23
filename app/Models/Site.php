<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'sites';
    protected $primaryKey = 'id';
   	public $timestamps = true;
   	//public $fillable = ['name','is_marked','marked_platform','expiry_time','owner'];
}
