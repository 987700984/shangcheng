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
class AuctionGoods extends Validate
{
    protected $rule = [
        'name'=>'require',
        'brand_id'=>'require',
        'auction'=>'require'
    ];

    protected $message = [
        'name.require'=>'拍品名称必填',
        'brand_id.require'=>'拍品作者必选',
        'auction.require'=>'拍品信息必填'
    ];

}
?>