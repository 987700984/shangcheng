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
 
namespace osc\app\controller;
use think\Db;
use osc\common\model\Member;
use osc\common\model\JournalAccount;
use osc\common\service\Order as OrderService;
use osc\common\model\Order as OrderModel;

class Order extends APP
{
	protected function _initialize(){
		parent::_initialize();

	}


	function index(){
		$order = new OrderService();
		$id=input('post.id');
		if ($id!='') {
			$id = input('post.id');
			$param = ['status'=>$id];

			$list=Db::name('order')
					->where(['uid'=>input('uid'),'order_status_id'=>$id])
					->order('order_id desc')
					->select();
			
			
		}else{
			$id=0;
			$list=Db::name('order')
					->where('uid',input('uid'))
					->order('order_id desc')
					->select();
		}
		$lis=array();
		foreach ($list as $key => $v) {
					$lis[$key]=$v;
					$lis[$key]['date_added']=date("Y-m-d H:i:s",$v['date_added']);
					$lis[$key]['pay_time']=date("Y-m-d H:i:s",$v['pay_time']);
		}
		$good = Db::view(['OrderGoods','o'],'*')->view('Goods','image,origin_price','Goods.goods_id=o.goods_id')->select();
		$goods=$this->handle_img($good,'image',100,100);
		return ['code'=>400,'msg'=>'获取数据成功','data'=>['id'=>$id,'goods'=>$goods,'order'=>$lis]];


	}
//生成订单
	public function order_add(){
		if (request()->isPost()) {
			$post = input('post.');
			$address = Db::name('address')->where(['address_id'=>$post['address_id']])->find();
			$arr['comment']   = $post['comment'];
			$arr['cart_id']   = explode(',',$post['cart_id']);
			$arr['is_points_goods']   = $post['is_points_goods'] ;
			$arr['total']   = $post['total'];

			$order = new OrderService();
			$orde=$order->order_ads($address,$arr,$post['uid']);
			$this->show('400','生成订单',$orde['order_id']);die;

			
		}
	}
//立即购买
	public function order_now(){
		if (request()->isPost()) {
			$post = input('post.');
			// var_dump($post);die;
			$address = Db::name('address')->where(['address_id'=>$post['address_id']])->find();
			$arr['comment']   = $post['comment'];
			$arr['is_points_goods']   = $post['is_points_goods'];
			$arr['goods_id']   = $post['goods_id'];
			$arr['quantity']   = $post['quantity'];
			$arr['total']   = $post['total'];
			$order = new OrderService();
			$status=$order->order_now($address,$arr,$post['uid']);
			return ['code'=>400,'msg'=>'生成订单','data'=>$status['order_id']];

		}
	}
	  public function order_handle(){
		  osc_order()->update_order(input('order_id'));
		  osc_order()->handle_point(input('order_id'), input('uid'));
		  $mem=Member::get(input('uid'));
		  $mem->storage_user_action('成功支付了订单');
		  $this->show('400','支付成功',$mem);

	  }


	
	
	function order_info(){
		if(!$order=osc_order()->order_info(input('param.id'),input('post.uid'))){
			return ['code'=>404,'msg'=>'非法操作'];
		}
		// dump($order);die;

			$order['order']['date_added']=date('Y-m-d H:i:s',$order['order']['date_added']);
			$order['order']['pay_time']=date('Y-m-d H:i:s',$order['order']['pay_time']);
			$order['order']['date']=date('Y-m-d H:i:s',$order['order']['date']);
			$order['order']['date_modified']=date('Y-m-d H:i:s',$order['order']['date_modified']);


		$product=$this->handle_img($order['order_product'],'image',100,100);
		return ['code'=>400,'msg'=>'获取数据成功','data'=>['order'=>$order['order'],'product'=>$product]];
	}


	function cancel_order(){
		$order=new OrderService();

		$order->cancel_order((int)input('param.order_id'),input('post.uid'));
		return ['code'=>400,'msg'=>'订单已取消'];
	}
	public  function charge_handle(){
		$journal_account = new JournalAccount([
				'amount' => input('param.total') ,
				'user_id' => input('param.uid'),
				'create_time'=>time(),
				'type' => 1,
		]);
		$journal_account->save();
		Db::name('member')->where('uid', input('param.uid'))->setInc('cash_points', input('param.number'));
		$mem=Member::get([input('param.uid')]);
		return $mem;
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





	public function points(){
		if (request()->isPost()) {
			$post=input('post.');
			$or=OrderModel::get($post['order_id']);
			if($or['order_status_id']!=config('default_order_status_id')){
				return ['code'=>404,'msg'=>'您已付款'];
			}
			$member=Member::get($post['uid']);
			if ($post['pay_points']>$member['cash_points']) {
				return ['code'=>404,'msg'=>'您的兑换券不足'];
			}else{
				$pay=Db::name('member')->where('uid',$post['uid'])->setDec('cash_points',$post['pay_points']);
				$ord_mod=new OrderModel();
				if ($pay) {
					//库存自减
					$goods = Db::name('order_goods')->where('order_id',$post['order_id'])->select();
					foreach ($goods as $key => $val) {
						Db::name('goods')->where('goods_id',$val['goods_id'])->setDec('quantity',$val['quantity']);
					}
					$ord_mod->save(['order_status_id'=>config('paid_order_status_id'),
							'order_num_alias'=>build_order_no(),'pay_time'=>time()],['order_id'=>$post['order_id']]);
					$mem=Member::get($post['uid']);
					return ['code'=>400,'msg'=>'兑换成功','data'=>$mem['cash_points']];
				}else{
					return ['code'=>404,'msg'=>'购买失败'];
				}
			}
		}
	}
	//删除订单
	public function order_del(){
		$del=OrderModel::destroy(input('post.order_id'));
		Db::name('order_goods')->where('order_id',input('post.order_id'))->delete();
		Db::name('order_history')->where('order_id',input('post.order_id'))->delete();
		if ($del) {
			return ['msg'=>'删除成功！'];
		}else{
			return ['msg'=>'操作失败'];
		}

	}

	//	确认收货处理
	public function confirm_ship(){
		$status = config('complete_order_status_id');
		$check = Db::name('order')->where('order_id',input('post.order_id'))->update(['order_status_id'=>$status,'date_modified'=>time()]);
		return ['msg'=>'订单已完成！！'];
	}

	//查询快递单号
	public function courier(){
		$cur = Db::name('order_history')->where('order_id',input('post.order_id'))->order('order_history_id desc')->find();
		if ($cur) {
			return $cur;
		}else{
			return ['comment'=>'快递单号查询失败'];
		}
	}

}
