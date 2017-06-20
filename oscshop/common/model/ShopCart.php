<?php
namespace osc\common\model;
use osc\common\model\Cart;
class ShopCart  {

    /**
     * @var false|\PDOStatement|string|\think\Collection
     */
    private $_cart_items;
    private $_uid;
    private $_type;

    public function __construct($uid,$type='money')
    {
        $this->_uid = $uid;
        $this->_type = $type;
        $this->_init_cart_items();
    }

    private function _init_cart_items()
    {
        $cart_items = Cart::where(['uid'=>$this->_uid,'type'=>$this->_type])->select();
        foreach($cart_items as $item){
            if(!$item->goods)
            {
                $item->delete();
            }else{
                $this->_cart_items[] = $item;
            }

        }
    }

    /**
     * @return Cart[]
     * */
    public function get_items()
    {

        if(!$this->_cart_items){
            $this->_cart_items = [];
        }
        return $this->_cart_items;
    }

    public function count_cart_total()
    {
        $cart_total = 0;
        if($this->get_items()){
            foreach($this->get_items() as $item) {
                $cart_total += $item->quantity;
            }
        }

        return $cart_total;
    }

    public function count_pay_points()
    {
        $points=0;
        foreach ($this->get_items() as $item) {
            $points += $item->get_total_pay_points();
        }

        return $points;

    }

    public function count_return_poionts()
    {
        $return_points = 0;
        foreach($this->get_items() as $item) {
            $return_points += $item->get_total_return_points();
        }
        return $return_points;
    }

    public function need_shipping()
    {
        foreach($this->get_items() as $item)
        {
            if($item->need_shipping())
            {
                return true;
            }
        }
        return false;
    }

    //取得商品重量
    public function get_weight() {
        $weight = 0;
        foreach ($this->get_items() as $item) {

            if ($item->need_shipping()) {
                $weight += $item->calculate_converted_total_weight();
            }
        }

        return $weight;
    }

    public function calculate_subtotal() {
        $subtotal = 0;
        foreach ($this->get_items() as $item) {
            $subtotal += $item->calculate_total_price();
        }
        return $subtotal;
    }

    public function calculate_shipping_fee($shipping_method,$shipping_city_id)
    {
        //积分兑换免运费
        if($this->_type == 'points') {
            return 0;
        }

        $not_free_weight = 0;
        $fixed_shipping_fee = 0;
        foreach($this->get_items() as $item) {
            if($item->need_shipping() ) {
                //固定邮费不为空则加固定邮费
                if(!is_null($item->goods->postage)) {
//                    $fixed_shipping_fee += $item->goods->postage * $item->quantity;
/* 艺拍项目客户要求不管多少数量只加一交运费*/
                                        $fixed_shipping_fee += $item->goods->postage ;

                } else{
                    $not_free_weight += $item->calculate_converted_total_weight();
                }
            }
        }

        $transport_fee = osc_transport()->calc_transport($shipping_method,$not_free_weight,$shipping_city_id);
        return $transport_fee['price'] + $fixed_shipping_fee;
    }



}