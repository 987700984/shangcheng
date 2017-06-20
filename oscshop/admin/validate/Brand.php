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
class Brand extends Validate
{
    protected $rule = [
        'name'  =>  'require|min:2|unique:brand',  
    ];

    protected $message = [
        'name.require'  =>  '品牌名称必填',
        'name.min'  =>  '品牌名称不能小于两个字',     
        'name.unique'  =>  '品牌名称已存在',
    ];

	
}
?>