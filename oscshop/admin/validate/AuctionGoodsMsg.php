<?php
/**
 * Waynes电子商务系统
 *
 * ==========================================================================
 * @link      http://www.waynes-tech.com
 * @copyright Copyright (c) 2015-2016 深圳市韦恩斯科技有限公司

 * ==========================================================================
 *
 * @author    深圳韦恩斯科技有限公司
 *
 */
namespace osc\admin\validate;
use think\Validate;
class AuctionGoodsMsg extends Validate
{
    protected $rule = [
        'auction_begintime'=>'require',
        'auction_endtime'=>'require',
        'origin_price'=>'require|number',
        'mark_up'=>'require|number',
    ];

    protected $message = [
        'auction_begintime.require'=>'起拍时间必填',
        'auction_endtime.require'=>'结拍时间必填',
        'origin_price.require'=>'起步价必填',
        'origin_price.number'=>'起步价必须为数字',
        'mark_up.require'=>'加价幅度必填',
        'mark_up.number'=>'加价幅度必须为数字'
    ];
}
?>