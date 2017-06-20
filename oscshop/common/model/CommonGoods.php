<?php
namespace osc\common\model;

use think\Model;

class CommonGoods extends Goods {

    protected $table = 'osc_goods';


    protected function base($query)
    {
        $query->where('is_auction',0);
    }

}