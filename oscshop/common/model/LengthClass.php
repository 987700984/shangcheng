<?php
namespace osc\common\model;

use think\Model;

class LengthClass extends Model {

//与商品表一对多
    public function goods(){
        //    与出价表一对多
            return $this->hasMany('Goods','goods_id');
    }

}