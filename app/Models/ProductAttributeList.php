<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeList extends Model
{
    protected $table = 'product_attribute_list';
    public $timestamps = false;
    protected $primaryKey = 'id';

    public function hass()
    {
        return $this->hasOne('App\Models\Attribute', 'attribute_id', 'id');
    }
}
