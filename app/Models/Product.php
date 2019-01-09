<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $appends = ['product_type_name', 'index_thum_path'];

    public function getProductTypeNameAttribute()
    {
        $id = $this->attributes['type'];
        return ProductType::where('id', $id)->value('name');
    }
    public function getIndexThumPathAttribute()
    {
        $id = $this->attributes['id'];
        return DB::table('resources as r')->join('product_resource as pr', 'r.id', '=', 'pr.resource_id', 'left')
            ->where('pr.product_id', $id)->where('pr.is_index', 1)->value('r.thum_path');
    }
    public function resources()
    {
        return $this->belongsToMany(Resource::class, 'product_resource', 'product_id', 'resource_id');
    }

    public function index_thumb()
    {
        return $this->belongsToMany(Resource::class, 'product_resource', 'product_id', 'resource_id')->where('is_index',1)->limit(1);
    }
    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute_list', 'product_id', 'attribute_id')->distinct('attribute_id');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'product_id', 'id');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collections_products', 'products_id', 'collections_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag', 'product_id', 'tag_id');
    }
    public static function attribute_values($id, $attribute_id = null)
    {
        $list = ProductAttributeList::with('attributeListHasAttribute')->where('product_id', $id)->where(function ($query) use ($attribute_id) {
            if ($attribute_id && is_array($attribute_id)) {
                $query->whereIn('attribute_id', $attribute_id);
            } elseif($attribute_id) {
                $query->where('attribute_id', $attribute_id);
            }
        })->get()->toArray();
        $attributes = [];
        foreach ($list as $value) {
            if (!isset($attributes[$value['attribute_list_has_attribute']['id']])) {
                $attributes[$value['attribute_list_has_attribute']['id']] = $value['attribute_list_has_attribute'];
            }
            $attributes[$value['attribute_list_has_attribute']['id']]['options'][] = ['id' => $value['id'], 'attribute_value' => $value['attribute_value'],'attribute_english_value' => $value['attribute_english_value']];
        }
        return $attributes;
    }

    public static function product_attribute_list($id)
    {
//        $attribute_value_list = self::attribute_values($id);
        $list = ProductAttribute::where('product_id', $id)->get();
        foreach ($list as $value) {
            $ids = explode(',', $value->attribute_list_ids);
            $value->attribute_list = ProductAttributeList::with('attributeListHasAttribute')->whereIn('id', $ids)->get();
        }
        return $list;
    }
}
