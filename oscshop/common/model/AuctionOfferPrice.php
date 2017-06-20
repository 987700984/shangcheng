<?php
namespace osc\common\model;

use think\Model;

class AuctionOfferPrice extends Model {

    //    只开启自动写入创建时间字段，关闭修改时间字段
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = false;

//与用户表关联 一对多
    public function Member()
    {
        return $this->belongsTo('Member','user_id');
    }
}