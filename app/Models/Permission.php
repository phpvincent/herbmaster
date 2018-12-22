<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /** 登陆处理返回权限
     * @param $data
     * @param $parent_id
     * @return array
     */
    public static function getPermission($data,$parent_id)
    {
        $tree = [];
        if(!empty($data)){
            foreach($data as $k => $v)
            {
                if($v->parent_id == $parent_id)
                {
                    $result['name'] = $v->name;
                    $result['meta'] = unserialize($v->meta);
                    $children  = self::getPermission($data, $v->id);
                    if(!empty($children)){
                        $result['children'] = $children;
                    }
                    array_push($tree,$result);
                }
            }
        }
        return $tree;
    }
}
