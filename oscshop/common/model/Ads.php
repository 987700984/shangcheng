<?php
namespace osc\common\model;

use think\Model;

class Ads extends Model {



    //与广告条目表关联 一对多
    public function adsItems()
    {
        return $this->hasMany('AdsItems','ad_id');
    }



}