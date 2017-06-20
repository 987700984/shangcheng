<?php
namespace osc\common\model;

use think\Model;

class GoodsOptionValue extends Model {
    protected $pk = 'goods_option_value_id';

    public function optionValue()
    {
        return $this->belongsTo('OptionValue');
    }

    public function goodsOption()
    {
        return $this->belongsTo('GoodsOption');
    }

    public function calculate_price()
    {
        if ($this->price_prefix == '+') {
            return  $this->price;
        } elseif ($this->price_prefix == '-') {
            return $this->price * -1;
        }
        return 0;
    }

    public function calculate_weight()
    {
        if ($this->weight_prefix == '+') {
            return   $this->weight;
        } elseif ($this->weight_prefix == '-') {
            return $this->weight * -1;
        }
        return 0;
    }

    public function has_enough_stock($need_quantity)
    {
        if($this->subtract && (!$this->quantity || ($this->quantity < $need_quantity)))
        {
            return false;
        }
        return true;
    }



}