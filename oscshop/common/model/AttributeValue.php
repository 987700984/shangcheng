<?php
namespace osc\common\model;

use think\Model;

class AttributeValue extends Model {

    //与属性值表关联 多对多
    public function goods()
    {
        return $this->belongsToMany('goods','osc_goods_attribute','goods_id','attribute_value_id');
    }
}