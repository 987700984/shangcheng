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
 
namespace osc\member\controller;
use osc\common\controller\HomeBase;
use \think\Validate;
use think\Db;
use osc\member\service\User;
use osc\payment\service\Payment;
class Checkout extends HomeBase
{	
	//此类不需要权限验证，不继承 MemberBase
	protected function _initialize() {
		parent::_initialize();
		$user = User::get_logined_user();
		if(!$user){
			$this->error('请先登录','/cart');
		}
		define('UID',$user->uid);

		if ((!$user->carts()->count_cart_total()) ) {
			$this->error('您的购物车没有商品','/cart');			
		}
	}
		
	function index(){
		
		$cart=osc_cart();
		
		// 验证商品数量		
		$cart_list=Db::name('cart')->where('uid',UID)->select();
		
		foreach ($cart_list as $k => $v) {
			$param['option']=json_decode($v['goods_option'], true);
			$param['goods_id']=$v['goods_id'];
			$param['quantity']=$v['quantity'];

			if($error = $cart->check($param))
			{
				return $error;
			}
			
		}
		
		$shipping = User::get_logined_user()->carts()->need_shipping();
		

		
		$this->assign('shipping_required',$shipping);
		$this->assign('SEO',['title'=>'商品结算-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		return $this->fetch();
	}
	//收货地址
	function shipping_address(){
		$user = User::get_logined_user();

		$s=session('shipping_address_id');
		
		if (isset($s)) {
			$this->assign('address_id',$s);
		} else {		
			$this->assign('address_id',$user->address_id);
		}
		
		$this->assign('province',Db::name('area')->where('area_parent_id',0)->select());
		$this->assign('addresses',User::get_address(UID));
		
		exit($this->fetch());	
	}
	
	//验证收货地址
	function validate_shipping_address(){

		$json=array();

		$cart = User::get_logined_user()->carts();

		$d=input('post.');
		
		if (isset($d['shipping_address']) && $d['shipping_address'] == 'existing') {
			
			if (empty($d['address_id'])) {
				
				$json['error']['warning'] ='请选择送货地址！！';
				
			} elseif (!in_array($d['address_id'], array_keys(User::get_address(UID)))) {
				
				$json['error']['warning'] = '无效地址！！';
			}
			if (!$json) {
				
				session('shipping_address_id',$d['address_id']);
			
				$address_info = Db::name('address')->where('address_id',$d['address_id'])->find();
				
				if ($address_info) {
					
					session('shipping_city_id',$address_info['city_id']);
			
					session('shipping_name',$address_info['name']);					
									
				} else {
					session('shipping_city_id',null);
					
				}
				session('shipping_method',null);					
			}
		}	

		if ($d['shipping_address'] == 'new') {			
			
			$validate = validate('Shipping');
		
			if(!$validate->check($d)){
			    return ['error'=>$validate->getError()];
			}			
			
			if (!$json) {						
		
				session('shipping_address_id',User::add_address($d));
				session('shipping_city_id',$d['city_id']);					
				session('shipping_method',null);										
				
			}
		}		

		return $json;	
	}

	//货运方式
	function shipping_method(){
		$user = User::get_logined_user();
		$shop_cart = $user->carts();

		
		$list=Db::name('transport')->select();
		$transport=[];
		if(isset($list)&&is_array($list)){
			foreach ($list as $k => $v) {
				$transport[$k]['id']=$v['id'];
				$transport[$k]['name']=$v['title'];
				$transport[$k]['info']=osc_transport()->calc_transport($v['id'],$shop_cart->get_weight(), session('shipping_city_id') );
			}
		}
		
		if(empty($list)){
			$transport['error']='系统没有配置货运方式！！！';
		}
		
		$this->assign('sm',$transport);
		exit($this->fetch());
	}
	
	//验证货运方式
	function validate_shipping_method(){		
	
		$json=[];
		
		$d=input('post.');
		
		if (!isset($d['shipping_method'])) {
			$json['error']['warning'] = '请选择货运方式！！';
		} else {
			//不存在
			if (!Db::name('transport')->find($d['shipping_method'])) {			
				$json['error']['warning'] ='非法操作！！';
			}
		}

		if (!$json) {
			
			session('shipping_method',$d['shipping_method']);
			session('comment',strip_tags($d['comment']));

		}
		
		return $json;				
	}	

	//支付方式
	function payment_method(){	
		
		$this->assign('list',Payment::get_available_payment_list());
	
		exit($this->fetch());
	}

	function validate_payment_method(){
		
		$json=[];		
		
		$d=input('post.');
		
		if(!isset($d['payment_method'])) {
			$json['error']['warning'] = '请选择支付方式！！';
		}elseif(!Db::name('config')->where(array('extend_value'=>$d['payment_method'],'status'=>1))->find()) {
			//不存在
			$json['error']['warning'] = '非法操作！！';
		}	
		
		if (!$json) {
			session('payment_method',$d['payment_method']);						
		}		
	
		return $json;
	}
	
	function confirm(){
		
		$cart=osc_cart();

		$cart_model = User::get_logined_user()->carts();
		
		//需要送货		
		if ($cart_model->need_shipping()) {
					
			$address_id=session('shipping_address_id');
			
			if ( isset($address_id)) {
				$shipping_address = Db::name('address')->find($address_id);		
			} 

			if (empty($shipping_address)) {								
				$redirect =url('/checkout');
			}

			//是否选定了支付方式	
			$shipping_method=session('shipping_method');
			if (!isset($shipping_method)) {
				$redirect =url('/checkout');
			}		
			$this->assign('shipping_required',true);	
		}else{
			
			$this->assign('shipping_required',false);
			session('shipping_method',null);
		}
		//是否有选择支付方法
		$payment_method=session('payment_method');
		
		if (!isset($payment_method)) {
			$redirect =url('/checkout');
		}
				
		// 验证商品数量		
		$cart_list=Db::name('cart')->where('uid',member('uid'))->select();
		
		foreach ($cart_list as $k => $v) {
			$param['option']=json_decode($v['goods_option'], true);
			$param['goods_id']=$v['goods_id'];
			$param['quantity']=$v['quantity'];

			if($error = $cart->check($param))
			{
				return $error;
			}
			
		}		
					
		
		if (!isset($redirect)) {
			
			if($products=$cart->get_all_goods(member('uid'))){
						
			//运费
			$transport_fee = $cart_model->calculate_shipping_fee(session('shipping_method'), session('shipping_city_id'));

			$this->assign('transport_fee',$transport_fee);

			
			$this->assign('products',$products);
			
			}

		}
		return $this->fetch();
	}
	
}
