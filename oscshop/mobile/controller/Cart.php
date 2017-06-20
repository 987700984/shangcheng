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
use osc\common\model\Cart as CartItem;
class Cart extends MobileBase
{
	protected function _initialize(){
		parent::_initialize();
		define('UID',User::is_login());
		
	}
		
    public function index()
    {		
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>member('uid')])->paginate(5);
//
		$good=array();
		foreach ($goods as $key => $v) {
			if ($v['end_time']!==NULL) {
				$good[$key]=$v;
				if ( strtotime($v['end_time'])<time() ) {
					$good[$key]['end_time']=1;

				}
			}
		}
		// var_dump($goods);die;
		$this->assign('list',$good);
		$this->assign('page',$goods->render());
		
		$this->assign('SEO',['title'=>'购物车(入会商品)-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }
 	public function ex_cart()
    {		
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.pay_points,w.origin_price,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>member('uid')])->paginate(5);
        // var_dump($goods);die;
		$this->assign('list',$goods);
		
		$this->assign('SEO',['title'=>'购物车(兑换商品)-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }
//兑换商品购物车结算
    public function ex_shop(){
    	if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
    	if (request()->isPost()) {
			$data = input('post.');
			$cart_id = $data['cart_id'];
			$list = array();
			foreach ($cart_id as $key => $v) {
				 $list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.pay_points,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>member('uid'),'a.cart_id'=>$v])->find();
				 if (!$list[$key]) {
					 	$this->error('购物车不存在');
				 }
			}
			$this->assign('SEO',['title'=>'购物车结算(兑换商品)-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
			$this->assign('list',$list);
			return $this->fetch();	
		}else{
			$this->error('参数错误！');
		}
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

					if ($list['end_time']!==NULL) {

						if ( strtotime($list['end_time'])<time() ) {
							$list['end_time']=1;

						}
					}

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
				$val['cash_point']=$val['cash_points']+1;

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
	//加入购物车
	public function add()
    {    
		
		if(!$uid=User::is_login()){
			return false;
		}else{
			$param=input('post.');
			$param['uid']=$uid;
			$param['quantity']=1;
			$param['create_time']=date('Y-m-d,H:i:s');
			$check = Db::name('cart')->where('goods_id',$param['goods_id'])->find();

			if ($check['goods_id']) {
				Db::name('cart')->where('cart_id',$check['cart_id'])->setInc('quantity',1);
			}else{
				Db::name('cart')->insert($param);	
			}
			
			return true;
		}
		
		
	
    }
    public function numb(){
		$cart=new CartItem();
		$cart->save(['quantity' => input('post.id')],['cart_id'=>input('post.cart_id')]);
		// return true;
		return 1;
	}
	public function remove(){
		$user = User::get_logined_user();
		if(!$user){
			return false;
		}else{
			$cart_item = CartItem::get(['cart_id'=>(int)input('param.id'),'uid'=>$user->uid]);
			if($cart_item)
			{
				$cart_item -> delete();
			}

			$user->storage_user_action('删除了购物车商品');
					
			return true;
		}
		
			
	}
	
}
