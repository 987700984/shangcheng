<?php
namespace osc\common\model;

use think\Model;
use osc\common\model\AuctionOfferPrice;

class Auctioning extends Model {

    //与商品表关联 一对一
    public function aucGoods()
    {
        return $this->hasOne('AucGoods','goods_id');
    }





    //    与出价表一对多
    public function auctionOfferPrice(){
        return $this->hasMany('AuctionOfferPrice','auctioning_id');
    }

    //与专场表关联 多对多
    public function auctionSpecialShow()
    {
        return $this->belongsToMany('AuctionSpecialShow','osc_auction_goods_special_show_relation','special_show_id','goods_id');
    }

    //与拍品-专场表关联 一对多
    public function auctionGoodsSpecialShowRelation()
    {
        return $this->hasMany('AuctionGoodsSpecialShowRelation','goods_id');
    }


    //通过时间比较及一口价的价价格比较来获得拍卖状态
    public function getStatusTextAttr($value,$data)
    {
        $offer_price = AuctionOfferPrice::where(['auctioning_id'=>$data['goods_id']])->order('price desc')->find();

        if(time()<=strtotime($data['auction_begintime'])){
            return '预拍中';
        }elseif(time()>=strtotime($data['auction_endtime'])){

            return '已结束';
        }elseif($data['fixed_price']&&$offer_price&&$data['fixed_price']<=$offer_price->price){
            return '已结束';
        }{
            return '竞拍中';
        }
    }


//    获得当前最高价
    public function getMaxPriceAttr($value,$data){
        $top = $data['origin_price'];
        if($this->auctionOfferPrice){
            foreach($this->auctionOfferPrice as $k => $v){
                if($top<$v->price)
                {
                    $top = $v->price;
                }
            }
        }
        return $top;
    }


//    获得出价总人次
    public function getTotalNumAttr($value,$data){
        $auctioning = self::get(['goods_id'=>$data['goods_id']]);
        $sum = 0;
        if($auctioning->auctionOfferPrice){
            $sum = count($auctioning->auctionOfferPrice);
        }
        return $sum;
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