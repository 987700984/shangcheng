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
 * 电脑版本
 */
 
namespace osc\payment\controller;
use osc\common\controller\Base;
use osc\common\model\Member;
use think\Db;
use osc\payment\service\Payment as PaymentService;
use osc\member\service\User;
class Payment extends Base{

	
	function pay_api(){
		if(request()->isPost()){
		
			$type=session('payment_method');
			
			$class = '\\osc\\payment\\controller\\' . ucwords($type);
				
			$payment= new $class();

			User::get_logined_user()->storage_user_action('下了订单，未支付');
			
			$url=$payment->process();
			
			return $url;
		
		}
	}
	
	function choice_payment_type(){
		
		$map['order_id']=['eq',(int)input('param.order_id')];
		$map['uid']=['eq',member('uid')];
		
		if(!$order=Db::name('order')->where($map)->find()){
			$this->error('订单不存在！！');
		}
		
		session('re_pay_order_id',$order['order_id']);
		
		$this->assign('list',PaymentService::get_available_payment_list());
		
		return $this->fetch('payment_list'); 
	}

	function re_pay(){
		if(request()->isPost()){
		
			$type=input('param.type');
			
			$class = '\\osc\\payment\\controller\\' . ucwords($type);
				
			$payment= new $class();
			
			$return=$payment->re_pay(input('post.id'));

			User::get_logined_user()->storage_user_action('点击了去支付');
			
			return ['type'=>$return['type'],'pay_url'=>$return['pay_url']];
		
		}
	}

	function charge_pay(){
		if(request()->isPost()){

			$type=input('param.type');

			$class = '\\osc\\payment\\controller\\' . ucwords($type);

			$payment= new $class();

			$return=$payment->charge_pay(input('post.'));
			$men=Member::get(member('uid'));
			$men->storage_user_action('充值入会');

			return ['type'=>$return['type'],'pay_url'=>$return['pay_url']];

		}
	}
}
