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
use think\Db;
use osc\member\service\User;
use osc\common\service\Order as OrderService;
use osc\common\model\Goods;
use osc\common\model\Order as OrderModel;

class Order extends MobileBase
{
	protected function _initialize(){
		parent::_initialize();
		define('UID',User::is_login());
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
	}


	function index(){
		$order = new OrderService();
		if (request()->isGet()) {
			$id = input('param.id');
			$param = ['status'=>$id];

			$list  = $order->order_list($param,10,UID);
			
			
		}else{
			$list = $order->where('uid',UID)->paginate(10);
		}
		$lis=array();
		foreach ($list as $key => $v) {
					$lis[$key]=$v;
					$lis[$key]['date_added']=date("Y-m-d H:i:s",$v['date_added']);
					$lis[$key]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
		}		
		$goods = Db::view(['OrderGoods','o'],'*')->view('Goods','image,origin_price','Goods.goods_id=o.goods_id')->select();
		if(in_wechat()){
			$this->assign('signPackage',wechat()->getJsSign(request()->url(true)));	
		}

		$this->assign('list',$lis);
		$this->assign('id',input('param.id'));
		$this->assign('goods',$goods);
		$this->assign('page',$list->render());
		$this->assign('SEO',['title'=>'订单列表 - '.config('SITE_TITLE')]);
		return $this->fetch();
	}
//生成订单
	public function order_add(){
		if (request()->isPost()) {
			$post = input('post.');
			$address = Db::name('address')->where(['address_id'=>$post['address_id']])->find();
			$arr['comment']   = $post['comment'];
			$arr['cart_id']   = $post['cart_id'];
			$arr['is_points_goods']   = $post['is_points_goods'] ;
			$arr['total']   = $post['total'];
			$order = new OrderService();
			$ord_mod=new OrderModel();
			$status=$order->order_ads($address,$arr,member('uid'));
			$ord  = Db::name('order')->order('order_id desc')->where('uid',member('uid'))->find();
			if ($arr['is_points_goods']==0) {

				$this->redirect('Payment/weixin_pay',$status);
			}else{
				
				$this->redirect('Order/pay_point',['order_id' => $ord['order_id']]);

			}
			
		}
	}
//立即购买
	public function order_now(){
		if (request()->isPost()) {
			$post = input('post.');
			// var_dump($post);die;
			$address = Db::name('address')->where(['address_id'=>$post['address_id']])->find();
			$arr['comment']   = $post['comment'];
			if ($post['type']=='money') {
				$arr['is_points_goods']   = 0 ;
			}else{
				$arr['is_points_goods']   = 1 ;
			}
			$arr['goods_id']   = $post['goods_id'];
			$arr['quantity']   = $post['quantity'];
			$arr['total']   = $post['total'];
			$order = new OrderService();
			$ord_mod=new OrderModel();
			$status=$order->order_now($address,$arr,member('uid'));
			$ord  = Db::name('order')->order('order_id desc')->where('uid',member('uid'))->find();
			if ($arr['is_points_goods']==0) {

				$this->redirect('Order/pay',['order_id' => $ord['order_id']]);
			}else{
				
				$this->redirect('Order/pay_point',['order_id' => $ord['order_id']]);

			}
		}
	}



	
	
	function order_info(){
		if(!$order=osc_order()->order_info(input('param.id'),UID)){
			$this->error('非法操作！！');
		}
		// dump($order);die;
		if (in_wechat()) {
			$wechat = wechat();
			$this->assign('signPackage', $wechat->getJsSign(request()->url(true)));
		}
		$this->assign('order',$order['order']);
		$this->assign('product',$order['order_product']);
		$this->assign('SEO',['title'=>'订单详情 - '.'点对点商城']);

		$this->assign('top_title','订单详情');
		return $this->fetch();
	}
	function cancel_order(){
		$order=new OrderService();
		$order->cancel_order((int)input('param.order_id'),UID);
		User::get_logined_user()->storage_user_action('取消了订单');
		return 1;
	}





	/*由于微信发起支付的路径只能有三个，为了节省用，将所有order定单里发起支付的页面全指向了这一个页面*/
	function pay(){

		$order = OrderModel::get(['order_id'=>(int)input('id')]);
		$this->assign('SEO',['title'=>'支付 - '.'点对点商城']);

		$this->assign('order',$order);
		if(in_wechat()){
			$this->assign('signPackage',wechat()->getJsSign(request()->url(true)));
		}


		return $this->fetch();
	}

	public function pay_point(){
		if (request()->isPost()) {
			$post=input('post.');
			if ($post['pay_points']>member('cash_points')) {
					$this->error('您的兑换券不足，请充值！');
				}else{
					$pay=Db::name('member')->where('uid',UID)->setDec('cash_points',$post['pay_points']);
					$ord_mod=new OrderModel();
					if ($pay) {
						//库存自减
						$goods = Db::name('order_goods')->where('order_id',$post['order_id'])->select();
						foreach ($goods as $key => $val) {
							Db::name('goods')->where('goods_id',$val['goods_id'])->setDec('quantity',$val['quantity']);
						}
						$ord_mod->save(['order_status_id'=>config('paid_order_status_id'),
							'order_num_alias'=>build_order_no(),'pay_time'=>time()],['order_id'=>$post['order_id']]);
						$this->success('兑换成功！！',url('Order/index'));
					}else{
						echo '购买失败！';die;
					}
				}
		}else{
			if (input('param.order_id')) {
				$order_id =input('param.order_id');
				$order    = OrderModel::get($order_id);
				$this->assign('SEO',['title'=>'兑换 - '.'点对点商城']);
				$this->assign('order',$order);
				$this->assign('point',member('cash_points'));
				return $this->fetch();	
			}else{
				$this->error('订单不存在！');
				
			}
			
		}
		
		
	}
	//删除订单
	public function order_del(){
		$del=OrderModel::destroy(input('post.order_id'));
		Db::name('order_goods')->where('order_id',input('post.order_id'))->delete();
		Db::name('order_history')->where('order_id',input('post.order_id'))->delete();
		if ($del) {
			return ['success'=>'删除成功！'];
		}else{
			return ['error'=>'操作失败'];
		}

	}

	//	确认收货处理
	public function confirm_ship(){
		$status = config('complete_order_status_id');
		$check = Db::name('order')->where('order_id',input('post.order_id'))->update(['order_status_id'=>$status,'date_modified'=>time()]);
		return ['success'=>'订单已完成！！'];
	}

	//查询快递单号
	public function courier(){
		$cur = Db::name('order_history')->where('order_id',input('post.order_id'))->order('order_history_id desc')->find();
		if ($cur) {
			return $cur;
		}else{
			return ['error'=>'快递单号查询失败'];
		}
	}

}
