<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'tags';
    public $timestamps = false;
}
