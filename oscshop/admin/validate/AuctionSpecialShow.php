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
class AuctionSpecialShow extends Validate
{
    protected $rule = [
        'img'=>'require',
        'title'=>'require',
        'product_agency'=>'require',
        'auction_begintime'=>'require',
        'auction_endtime'=>'require',
    ];

    protected $message = [
        'title.require'=>'专场名称必填',
        'img.require'=>'专场图片不能为空',
        'product_agency.require'=>'出品方必填',
        'auction_begintime.require'=>'预展时间必填',
        'auction_endtime.require'=>'竞拍时间必填',
    ];

}
?>