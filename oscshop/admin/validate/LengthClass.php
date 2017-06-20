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
class LengthClass extends Validate
{
    protected $rule = [
        'title'  =>  'require|min:2|unique:length_class',
        'unit'=>'require',
        'value'=>'float|require'    
    ];

    protected $message = [
        'title.require'  =>  '长度名称必填',
        'title.min'  =>  '长度名称不能小于两个字',     
        'title.unique'  =>  '长度名称已存在',         
		'unit.require'  =>  '单位必填',		
        'value.float'  =>  '值必须是数字', 
        'value.require'  =>  '值必填' 
    ];

	
}
?>