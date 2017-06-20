<?php
namespace osc\common\model;

use think\Model;

class GoodsDescription extends Model {
    protected $pk = 'goods_description_id';


//与评论表关联 一对多
    public function goodsComment()
    {
        return $this->hasMany('GoodsComment','goods_id');
    }

}