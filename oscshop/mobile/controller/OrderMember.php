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
class OrderMember extends MobileBase{
	
	protected function _initialize(){
		parent::_initialize();
		define('UID',User::is_login());
		// if(!in_wechat()){
		// 		echo '请用微信打开';die;
		// }
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
	}
	
	function index(){
		$this->assign('status',Db::name('order_status')->select());
		$this->assign('list',osc_order()->order_list(input('param.'),20,member('uid')));
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		return $this->fetch();
	}
	
//购物车结算
	public function order(){
		if (request()->isPost()) {
			$data = input('post.');
			$cart_id = $data['cart_id'];
			$list = array();
			foreach ($cart_id as $key => $v) {
			 	$list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>member('uid'),'a.cart_id'=>$v])->find();
				if (!$list[$key]) {
				 	$this->error('购物车不存在');
				}
			}
			$this->assign('list',$list);
			$this->assign('SEO',['title'=>'结算 - '.config('SITE_TITLE')]);
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
    		$this->assign('SEO',['title'=>'结算 - '.config('SITE_TITLE')]);
			return $this->fetch();
		}
    }


	public function ticketTime(){
		$order = Db::name('goods_ticket')->where('uid',member('uid'))->where('start_time','>',strtotime("-1years",time()))->select();
		foreach ($order as $key => $val) {

			$order[$key]['start_time']=strtotime("1months",$val['start_time']);
			$order[$key]['end_time']=strtotime("1years",$val['start_time']);
			if ($val['category_id']==28) {
				$val['cash_point']=$val['cash_points'];

			}

			$order[$key]['total_points']=$val['cash_point']+$val['none_points'];
		}
		// dump($order);die;
		$this->assign('SEO',['title'=>'兑换账期 - '.config('SITE_TITLE')]);
		$this->assign('order',$order);
		$this->assign('points',member('points'));
		$this->assign('point',member('cash_points'));
		return $this->fetch();
	}
}
