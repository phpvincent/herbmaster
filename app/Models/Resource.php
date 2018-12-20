<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    public static function get_allow_filetype()
    {
    	return ['jpg','jpeg','png','mp4','gif','xls','xlsx'];
    }
    public static function get_size($num)
    {
    	return sprintf("%.2f",$num/1024/1024);
    }
}
