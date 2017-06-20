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
 
namespace osc\mobile\controller;
use osc\mobile\validate\Address;
use \think\Db;
use \think\Controller;
use osc\member\service\User;
use osc\common\model\Goods;
use osc\common\model\Member;
class WeixinService extends Controller{

	function index(){
		import('ChatCallback', EXTEND_PATH.'/payment/weixin');
		$weixin = new \ChatCallback();
		$token=load_config();
		$weixin->valid($token['token']);
	}
}
