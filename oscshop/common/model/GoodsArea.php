<?php
namespace osc\common\model;

use think\Model;

class GoodsArea extends Model {

//与商品表关联 一对多
    public function Goods()
    {
        return $this->hasMany('Goods','goods_id');
    }
}