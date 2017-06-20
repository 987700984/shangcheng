<?php
namespace osc\common\model;

use think\Model;

class Category extends Model {

//与商品表关联 多对多
    public function Goods()
    {
        return $this->belongsToMany('Goods','osc_goods_to_category','goods_id','category_id');
    }

}