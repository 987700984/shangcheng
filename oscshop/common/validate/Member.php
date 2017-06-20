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
 * 用户注册验证
 */ 
namespace osc\common\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule = [
        'username'  =>  'require|min:2|unique:member',
        'password'=>'require|min:6',
    ];

    protected $message = [
        'username.require'  =>  '手机号必填',
        'username.min'  =>  '用户名不能小于两个字',     
        'username.unique'  =>  '手机号已经存在',
		'password.require'  =>  '密码必填',
		'password.min'  =>  '密码不能小于6位',
		
    ];
	
}
