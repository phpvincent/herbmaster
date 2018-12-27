<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'product_resource', 'product_id', 'resource_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_list', 'product_id', 'attribute_id')->distinct('attribute_id');
    }

    public static function attribute_values($id, $attribute_id = null)
    {
        $list = ProductAttributeList::with('attribute')->where('product_id', $id)->where(function ($query) use ($attribute_id) {
            if ($attribute_id) {
                $query->where('attributes.id', $attribute_id);
            }
        })->get()->toArray();
        $attributes = [];
        foreach ($list as $value) {
            if (!isset($attributes[$value['attribute']['id']])) {
                $attributes[$value['attribute']['id']] = $value['attribute'];
            }
            $attributes[$value['attribute']['id']]['values'][] = ['id' => $value['id'], 'attribute_value' => $value['attribute_value'],'attribute_english_value' => $value['attribute_english_value']];
        }
        return $attributes;
    }

    public static function product_attribute_list($id)
    {
        $attribute_value_list = self::attribute_values($id);
        $list = ProductAttribute::where('product_id', $id)->get();
        foreach ($list as $value) {
            $ids = explode(',', $value->attribute_list_ids);
            $value->attribute_list = ProductAttributeList::with('attribute')->whereIn('id', $ids)->get();
        }
        return $list;
    }
}
