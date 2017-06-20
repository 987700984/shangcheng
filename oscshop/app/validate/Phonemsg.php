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
class Phonemsg extends Validate
{
    protected $rule = [
        'phone_number|手机号' => 'number|require|min:11',
    ];



}
?>