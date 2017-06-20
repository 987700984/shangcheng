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
use osc\common\controller\AdminBase;
use osc\common\model\Address;

use osc\common\model\Member;
use osc\common\model\Order;
use osc\common\model\OrderGoods;
use think\Db;
use osc\admin\service\User;
use wechat\OscshopWechat;
class OrderBackend extends AdminBase{

	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','会员');
		$this->assign('breadcrumb2','订单');
	}


     public function index(){

		$this->assign('status',Db::name('order_status')->select());
		$this->assign('list',osc_order()->order_list(input('param.'),20));
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		
    	return $this->fetch();
	 }
	
 	public function show_order(){

     	$data = osc_order()->order_info(input('param.id'));
		$data['order_statuses'] = Db::name('OrderStatus')->select();
		// dump($data);die;
		$this->assign('data',$data);
		$this->assign('crumbs','订单详情');

    	return $this->fetch('show');
	 }

	function print_order(){
		$this->assign('order',osc_order()->order_info(input('param.id')));
		return $this->fetch('order');
	 }

	function del(){
		osc_order()->del_order((int)input('param.id'));
		User::get_logined_user()->storage_user_action('删除了订单');
		$this->redirect('OrderBackend/index');
	}

	function history(){

		$model=osc_order();
		if(request()->isPost()){
			if(input('param.order_status_id')==config('cancel_order_status_id')){

				$model->cancel_order(input('param.id'));
				User::get_logined_user()->storage_user_action('取消了订单');

				$result=true;
			}else{
				$result=$model->add_order_history(input('param.id'),input('param.'));
//发模板消息给客户

				if(input('param.order_status_id')==config('shipped_order_status_id')){
					$order = Order::get(['order_id'=>input('id')]);
					$member = Member::get(['uid'=>$order->uid]);

					$order_goods = OrderGoods::get(['order_id'=>$order->order_id]);

					$this->send_shipped_msg($member,$order_goods,$order->order_num_alias);
				}
			}

			/**
			 * 判断是否选择了通知会员，并发送邮件
			 */
			if(input('param.notify')==1){

			}

			if($result){
				$this->success='新增成功！！';
			}else{
				$this->error='新增失败！！';
			}
		}

		$results = $model->get_order_histories(input('param.id'));

		foreach ($results as $result) {
			$histories[] = array(
					'notify'     => $result['notify'] ? '是' : '否',
					'status'     => $result['order_status_name'],
					'comment'    => nl2br($result['comment']),
					'date_added' => date('Y/m/d H:i:s', $result['date_added'])
				);
			}

			$this->histories=$histories;

			$this->assign('histories',$histories);

			exit($this->fetch());
	}

	function update_order(){
		$data=input('post.');
		$type=input('param.type');

		//更新 order_goods
		$og=Db::name('order_goods')->find($data['order_goods_id']);

		if($type=='quantity'){

			$update['quantity']=$data['quantity'];
			$update['total']=$data['quantity']*$og['price'];
			$update['order_goods_id']=$data['order_goods_id'];

		}elseif($type=='price'){

			$update['price']=$data['price'];
			$update['total']=$og['quantity']*$data['price'];
			$update['order_goods_id']=$data['order_goods_id'];

		}

		if(Db::name('order_goods')->update($update,false,true)){

			$total=0;
			//更新 order
			$order_goods=Db::name('order_goods')->where(array('order_id'=>$data['order_id']))->select();

			foreach ($order_goods as $k => $v) {
				$total+=$v['total'];
			}

			Db::name('order')->where(array('order_id'=>$data['order_id']))->update(array(
				'sub_total' => $total,
				'shipping_fee' => $data['shipping'],
				'total'=>$total+$data['shipping']));


			User::get_logined_user()->storage_user_action('更新了订单');

			return true;

		}
	}
	//更新运费
	function update_shipping(){

		$d=input('post.');

		$shipping=$d['shipping'];

		$order = Order::findOrFail($d['order_id']);


		if($order->shipping_fee!=$shipping){
			$order->shipping_fee = $shipping;
			$order->total = $order->sub_total + $shipping;

			$order->save();
			User::get_logined_user()->storage_user_action('更新了订单运费');
			return true;
		}


	}


//发货提醒客户
	private function send_shipped_msg($member,$order_goods,$order_num_alias){

		$wechat = OscshopWechat::getInstance([
				'appid'=>config('appid'),
				'appsecret'=>config('appsecret'),
				'token'=>config('token'),
				'encodingaeskey'=>config('encodingaeskey')]);

		$data=[
				'touser'=>$member->wechat_openid,
				'template_id'=>'zhlLB1qiGWttM0v2I80xwWwfFUQpmwYxTPE2Wy_jEXc',
				'url'=>'http://www.ssdq88.com/mobile/User/index',
				'topcolor'=>'#FF0000',
				'data'=>[
						'first'=>[
								'value'=>'你好，你的订单已发货，请留意查收。',
								'color'=>'#173177'
						],
						'keyword1'=>[
								'value'=>$order_num_alias,
								'color'=>'#173177'
						],
						'keyword2'=>[
								'value'=>$order_goods->name,
								'color'=>'#173177'
						],
						'keyword3'=>[
								'value'=>date('Y-m-d H:i:s',time()),
								'color'=>'#173177'
						],
						'remark'=>[
								'value'=>'去用户中心查看',
								'color'=>'#173177'
						],
				]
		];

		$result = $wechat->sendTemplateMessage($data);
	}


//	确认线下支付
	public function offline_confirm(){
		$order = Order::get(['order_id'=>input('order_id')]);
		$order->offline_confirm = 1;
		$result = $order->save();
		if($result){
			$phone = $order->tel;
			$address = Address::get(['address_id'=>1]);
			$str='管理员已经同意您的线下支付申请，请尽快去'.$address->address.'提货;'.'联系人'.$address->name.'电话：'.$address->telephone;
			import('ChuanglanSmsApi', EXTEND_PATH.'/phone');
			$clapi = new \ChuanglanSmsApi();
			$res = $clapi->sendSMS($phone,$str, 'true');
			$result = $clapi->execResult($res);
			if($result[1]==0) {
				$this->success('操作成功!');
			}else{
				$this->error('短信发送失败，请手动发送通知客户');
			}
		}else{
			$this->error('操作失败');
		}
	}



	//	确认定制
	public function custom_confirm(){
		$order = Order::get(['order_id'=>input('order_id')]);
		$order->custom_confirm = 1;
		$order->order_status_id = config('default_order_status_id');
		$result = $order->save();
		if($result){
			$phone = $order->tel;
			$address = Address::get(['address_id'=>1]);
			$str='管理员已经同意您的['.$order->pay_subject.']定制，请耐心等待';
			import('ChuanglanSmsApi', EXTEND_PATH.'/phone');
			$clapi = new \ChuanglanSmsApi();

			$res = $clapi->sendSMS($phone,$str, 'true');
			$result = $clapi->execResult($res);
			if($result[1]==0) {
				$this->success('操作成功!');
			}else{
				$this->error('短信发送失败，请手动发送通知客户');
			}
		}else{
			$this->error('操作失败');
		}
	}



//	邀请线下支付
	public function offline_suggest(){
		$order = Order::get(['order_id'=>input('order_id')]);
		$order->offline_confirm = 1;
		$order->payment_code = 'offline';
		$result = $order->save();
		if($result){
			$phone = $order->tel;
			$address = Address::get(['address_id'=>1]);
			$str='管理员邀请您线下支付，请尽快去'.$address->address.'提货;'.'联系人'.$address->name.'电话：'.$address->telephone;
			import('ChuanglanSmsApi', EXTEND_PATH.'/phone');
			$clapi = new \ChuanglanSmsApi();
			$res = $clapi->sendSMS($phone,$str, 'true');
			$result = $clapi->execResult($res);
			if($result[1]==0) {
				$this->success('操作成功!');
			}else{
				$this->error('短信发送失败，请手动发送通知客户');
			}
		}else{
			$this->error('操作失败');
		}


	}
}
