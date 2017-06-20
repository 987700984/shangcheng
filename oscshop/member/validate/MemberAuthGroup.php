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
 
namespace osc\member\validate;
use think\Validate;
class MemberAuthGroup extends Validate
{
    protected $rule = [
        'title'  =>  'require|min:2',     
    ];

    protected $message = [
        'title.require'  =>  '菜单名称必填',
        'title.min'  =>  '菜单名称不能小于两个字',     
    ];

	
}
?>