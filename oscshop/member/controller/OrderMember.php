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
use osc\common\controller\MemberBase;
use think\Db;
use osc\member\service\User;
class OrderMember extends MemberBase{
	
	protected function _initialize(){
		parent::_initialize();
		
		$this->assign('breadcrumb2','订单管理');
		$this->assign('breadcrumb1','我的订单');
		
	}
	
	function index(){
		$this->assign('status',Db::name('order_status')->select());
		$this->assign('list',osc_order()->order_list(input('param.'),20,member('uid')));
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		return $this->fetch();
	}
	
	public function show_order(){
     	
		if(!$order=osc_order()->order_info(input('param.id'),member('uid'))){
			$this->error('非法操作！！');
		}
		User::get_logined_user()->storage_user_action('查看了订单详情');
		$this->assign('data',$order);		
		$this->assign('crumbs','订单详情');
				
    	return $this->fetch('show');
	 }

	public function order(){
		if (request()->isPost()) {
			$data = input('post.');
			$cart_id = $data['cart_id'];
			$list = array();
			foreach ($cart_id as $key => $v) {
			 $list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>member('uid'),'a.cart_id'=>$v])->find();
			}
			$this->assign('list',$list);
			return $this->fetch();	
		}else{
			$this->error('参数错误！');
		}
			

		
	}
//产品入会立即购买
    public function member_shop(){
    	if (request()->isPost()) {
			$data = input('post.');
    		
    		$list=Db::name('goods')->where('goods_id',$data['goods_id'])->find();
    		if ($list['is_points_goods']==0) {
    			$this->assign('type','money');
    		}else{
    			$this->assign('type','points');
    		}
    		// var_dump($list);die;
    		$this->assign('list',$list);
			return $this->fetch();
		}
    }

	 public function history(){
		
	 		$model=osc_order();		
			
			$results = $model->get_order_histories(input('param.id'),member('uid'));
		
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
	function cancel(){				
		osc_order()->cancel_order((int)input('param.id'),UID);
		User::get_logined_user()->storage_user_action('取消了订单');
		$this->success('取消成功！！',url('OrderMember/index'));
	}

	public function ticketTime(){
		Db::name('goods_ticket')->where('uid',member('uid'))->where('end_time','>',date('Y-m-d:H:i:s'))->select();
		return $this->fetch();
	}
}
