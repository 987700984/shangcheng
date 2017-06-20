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
namespace osc\mobile\validate;
use think\Validate;
class Agent extends Validate
{
    protected $rule = [        
        'tel'  =>  'require|unique:agent_apply',
        'name'  =>  'require',
        'email'  =>  'require',
        'id_cart'  =>  'require|unique:agent_apply',
    ];

    protected $message = [        
        'tel.require'  =>  '手机号必填',
        'tel.unique'  =>  '手机号已经存在',
        'name.require'  =>  '姓名必填',
        'email.require'  =>   '电子邮件必填',
        'id_cart.require'  => '身份证必填',
        'id_cart.unique'  =>  '身份证已经存在',
    ];
	
}
?>