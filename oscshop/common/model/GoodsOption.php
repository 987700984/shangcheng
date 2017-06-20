<?php
namespace osc\common\model;

use think\Model;

class GoodsOption extends Model
{
    protected $pk = 'goods_option_id';

    public function goodsOptionValue()
    {
        return $this->hasMany('GoodsOptionValue');
    }
}