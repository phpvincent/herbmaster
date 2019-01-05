<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table = 'attributes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function values()
    {
        return $this->hasMany(ProductAttributeList::class, 'attribute_id');
    }
}
