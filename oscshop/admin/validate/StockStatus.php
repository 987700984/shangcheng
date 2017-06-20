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
class StockStatus extends Validate
{
    protected $rule = [
        'name'  =>  'require|min:2|unique:stock_status'
    ];

    protected $message = [
        'name.require'  =>  '库存状态必填',
        'name.min'  =>  '库存状态不能小于两个字',     
        'name.unique'  =>  '库存状态已存在'
    ];

	
}
?>