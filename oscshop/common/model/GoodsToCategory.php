<?php
namespace osc\common\model;

use think\Model;

class GoodsToCategory extends Model {

    public function goods()
    {
        return $this->belongsTo('Goods','goods_id');
    }


}