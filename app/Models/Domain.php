<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';
    protected $primaryKey = 'id';
   	public $timestamps = true;
   	public $fillable = ['name','is_marked','marked_platform','expiry_time','owner'];
}
