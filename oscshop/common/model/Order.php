<?php
namespace osc\common\model;

use think\Model;

class Order extends Model
{
    protected $pk = 'order_id';

//与商品厍表关联 一对多
    public function OrderHistory()
    {
        return $this->hasMany('orderHistory','order_id');
    }

    //与订单商品表关联 一对多
    public function OrderGoods()
    {
        return $this->hasMany('orderGoods','order_id');
    }
//  与商品表关系 一对一
    public function Goods(){
       return $this->hasOne('orderGoods','goods_id'); 
    }
}