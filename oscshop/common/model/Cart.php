<?php
namespace osc\common\model;

use think\Model;
use think\Db;

class Cart extends Model {
    protected $pk = 'cart_id';
    private $_goods_options = [];

    public function goods(){
        return $this->belongsTo('Goods','goods_id');
    }

    public function get_total_pay_points()
    {
        return $this->goods->pay_points * $this->quantity;
    }

    public function get_total_return_points()
    {
        return $this->goods->points * $this->quantity;
    }

    public function need_shipping()
    {
        return $this->goods->shipping;
    }

    public function getNeedShippingAttr()
    {
        return $this->need_shipping();
    }


    /**
     * @return GoodsOptionValue[]
     */
    public function goods_option_objects()
    {
        if(empty($this->_goods_options ) and $this->goods_option) {
            $goods_options = [];
            foreach ((array)(json_decode($this->goods_option)) as $goods_option_id => $option_value) {
                $option_id = (int)(explode(',', $goods_option_id)[1]);
                $option_object = GoodsOption::get(['goods_id'=>$this->goods_id,'option_id'=>$option_id]);

                if(!empty($option_object)){
                    $option_value_array = is_array($option_value)?$option_value:[$option_value];
                    $option_value_objects = $option_object->goodsOptionValue()->where('option_value_id','in',$option_value_array)->select();

                    foreach($option_value_objects as $option_value_object)
                    {
                        $goods_options[] = $option_value_object;
                    }
                }

            }

            $this->_goods_options = $goods_options;
        }
        return $this->_goods_options;
    }

    public function getGoodsOptionObjectsAttr()
    {
        return $this->goods_option_objects();
    }

    public function calculate_price(){
        $price = $this->goods->price;

        $discount=Db::query("SELECT price FROM " . config('database.prefix') . "goods_discount WHERE goods_id = '" . (int)$this->goods_id . "' AND quantity <=" . (int)$this->quantity . " ORDER BY quantity DESC, price ASC LIMIT 1");

        if($discount){
            $price=$discount[0]['price'];
        }

        $goods_options = $this->goods_option_objects();
        foreach($goods_options as $option ){
            $price += $option->calculate_price();
        }
        return $price;
    }

    public function calculate_total_price()
    {
        return $this->calculate_price() * $this->quantity;
    }

    public function calculate_weight()
    {
        $weight = $this->goods->weight;
        $goods_options = $this->goods_option_objects();
        foreach($goods_options as $option ){
            $weight += $option->calculate_weight();
        }
        return $weight;
    }

    public function calculate_total_weight()
    {
        return $this->calculate_weight() * $this->quantity;
    }

    public function calculate_converted_total_weight()
    {
         return osc_weight()->convert($this->calculate_total_weight(), $this->goods->weight_class_id,config('weight_id'));
    }

    public function has_enough_stock()
    {
        $goods_options = $this->goods_option_objects();
        foreach($goods_options as $goods_option) {
            if (!$goods_option->has_enough_stock($this->quantity)) {
                return false;
            }
        }
        return true;
    }
}