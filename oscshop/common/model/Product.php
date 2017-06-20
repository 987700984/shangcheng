<?php
namespace osc\common\model;

use think\Model;

class Product extends Model {
   
	public function images(){
		return $this->hasMany('ProductImage','pid');
	}
}