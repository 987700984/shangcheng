<?php
namespace osc\common\model;

use think\Model;

class AucGoods extends Goods {

    protected $table = 'osc_goods';

//与商品信息表关联 一对一
    public function auctioning()
    {
        return $this->hasOne('Auctioning','goods_id');
    }
}