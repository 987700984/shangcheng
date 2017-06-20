<?php
namespace osc\common\model;

use think\Model;
use think\Db;
use osc\common\model\ShopCart;
use osc\common\model\Order;
class Member extends Model {
    protected $pk = 'uid';

    public function storage_user_action($info)
    {
        storage_user_action($this->uid,empty($this->username)?$this->nickname:$this->username,config('FRONTEND_USER'),$info);
    }

    public function wishes()
    {
        return $this->hasMany('MemberWishlist', 'uid');
    }

    //与用户出价表关联 一对多
    public function AuctionOfferPrice()
    {
        return $this->hasMany('AuctionOfferPrice','id');
    }

    //与用户资金流动表关联 一对多
    public function JournalAccount()
    {
        return $this->hasMany('JournalAccount','user_id');
    }

    function order_count($status){
        return count(Db::name('order')->where(array('order_status_id'=>$status,'uid'=>$this->uid))->select());
    }

    public function carts($type='money')
    {
        return new ShopCart($this->uid,$type);
    }


//通过订单数量获得用户等级
    public function getUserLevelAttr($value,$data){
$order_finish_status = config('complete_order_status_id');
        $orders = Order::where(['uid'=>$data['uid'],'order_status_id'=>$order_finish_status])->select();
        $total_times = count($orders);
       $total_amount = 0;
        $level = '新手买家';
    if($orders) {
        foreach ($orders as $k => $v) {
$total_amount += $v->total;
        }
    }
        if($total_amount>=200000||$total_times>=100){
$level = "殿堂级藏家";
        }elseif($total_amount>=100000||$total_times>=50){
            $level = "大收藏家";
        }elseif($total_amount>=50000||$total_times>=30){
            $level = "高级藏家";
        }elseif($total_amount>=20000||$total_times>=20){
            $level = "中级藏家";
        }elseif($total_amount>=5000||$total_times>=10){
            $level = "初级藏家";
        }elseif($total_amount<=5000||$total_times>=1){
            $level = "新手买家";
        }

   return $level;


    }
}