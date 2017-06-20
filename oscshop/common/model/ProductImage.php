<?php
namespace osc\common\model;

use think\Model;

class ProductImage extends Model {
   public function product()
    {
        return $this->belongsTo('product');
    }
	
}