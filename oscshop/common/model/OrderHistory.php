<?php
namespace osc\common\model;

use think\Model;

class OrderHistory extends Model
{

//
//    public function getOrderTypeAttr($value)
//    {
//        $status = [0=>'普通商品',1=>'拍卖商品',2=>'定制商品'];
//        return $status[$value];
//    }


//与商品厍表关联 一对多
    public function order()
    {
        return $this->belongsTo('order','order_id');
    }


}