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
class EditAds extends Validate
{
    protected $rule = [
        'name'=>'require',
        'width'=>'require',
        'height'=>'require',
        'start_time'=>'require',
        'end_time'=>'require',
    ];

    protected $message = [
        'name.require'=>'广告位名称必填',
        'width.require'=>'宽度必填',
        'height.require'=>'高度必填',
        'start_time.require'=>'开始时间必填',
        'end_time.require'=>'结束时间必填',
    ];

}
?>