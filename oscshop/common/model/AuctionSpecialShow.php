<?php
namespace osc\common\model;

use think\Model;

class AuctionSpecialShow extends Model {

//通过时间比较来获得拍卖状态
    public function getStatusTextAttr($value,$data)
    {
        if(time()<=strtotime($data['auction_begintime'])){
            return '预拍中';
        }elseif(time()>=strtotime($data['auction_endtime'])){
            return '已结束';
        }else{
            return '竞拍中';
        }
    }


    //通过时间比较来获得拍卖数字状态
    public function getStatusTextNumAttr($value,$data)
    {
        if(time()<=strtotime($data['auction_begintime'])){
            return 0;
        }elseif(time()>=strtotime($data['auction_endtime'])){
            return 2;
        }else{
            return 1;
        }
    }

    //获得专场围观人数
    public function getViewsAttr($value,$data)
    {
        $AuctionSpecialShow = self::get(['id'=>$data['id']]);
        $views = 0;
       if($AuctionSpecialShow){
          foreach($AuctionSpecialShow->auctioning as $k => $v){
              $views += $v->views;
          }
       }
        return $views;
    }

//    获得出价人数
    public function getBidNumAttr($value,$data)
    {
        $AuctionSpecialShow = self::get(['id'=>$data['id']]);
        $bid_num = 0;
        if($AuctionSpecialShow){
//            dump($AuctionSpecialShow->auctioning);die;
            foreach($AuctionSpecialShow->auctioning as $k => $v){
                $bid_num += $v->bid_num;
            }
        }
        return $bid_num;
    }


    //与拍品信息表关联 多对多
    public function auctioning()
    {
        return $this->belongsToMany('Auctioning','osc_auction_goods_special_show_relation','goods_id','special_show_id');
    }


    //开场时间格式转换输出
    public function getAuctionBegintimeAttr($value)
    {
        return date('Y/m/d H:i:s',strtotime($value));
    }

    //结束时间格式转换输出
    public function getAuctionEndtimeAttr($value)
    {
        return date('Y/m/d H:i:s',strtotime($value));
    }





}