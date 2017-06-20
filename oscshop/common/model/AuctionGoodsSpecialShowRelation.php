<?php
namespace osc\common\model;

use think\Model;

class AuctionGoodsSpecialShowRelation extends Model {

    //与拍品信息表关联 一对多
    public function auctioning()
    {
        return $this->hasMany('Auctioning');
    }
}