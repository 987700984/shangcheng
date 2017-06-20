<?php
namespace osc\common\model;

use think\Model;

class MemberWishlist extends Model {
    protected $pk = array('uid','goods_id');

    public function goods()
    {
        return $this->belongsTo('Goods');
    }

}