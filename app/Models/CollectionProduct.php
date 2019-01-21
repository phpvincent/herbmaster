<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionProduct extends Model
{
    protected $table = 'collections_products';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
