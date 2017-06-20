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
class CustomGoods extends Validate
{
    protected $rule = [
        'name'=>'require',

    ];

    protected $message = [
        'name.require'=>'定制商品名称必填',
    ];

}
?>