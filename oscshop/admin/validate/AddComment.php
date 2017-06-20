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
class Addcomment extends Validate
{
    protected $rule = [
        'content'=>'require',
        'goods_id'=>'require|number',
        'username'=>'require'
    ];

    protected $message = [
		'content.require'  =>  '请填写评论内容！',
        'goods_id.require' => '非法操作!',
		'goods_id.number' => '非法操作!',
        'username.require'  =>  '请填写评论用户名！'
    ];
}
?>