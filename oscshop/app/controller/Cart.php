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
use think\Log;
use osc\common\model\Member;
use osc\common\model\Cart as CartItem;
class Cart extends APP
{
	protected function _initialize(){
		parent::_initialize();

		
	}
		
    public function index()
    {		

		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>input('post.uid')])->select();
//
		$good=array();
		foreach ($goods as $key => $v) {
				$good[$key]=$v;
			if ($v['end_time']!==NULL) {

				if ( strtotime($v['end_time'])<time() ) {
					$good[$key]['end_time']=1;

				}
			}
		}
		$good=$this->handle_img($good,'image',100,100);
		return ['code'=>400,'msg'=>'获取数据成功','data'=>['list'=>$good]];

   
    }
 	public function ex_cart()
    {
		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.pay_points,w.origin_price,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>input('post.uid')])->select();
        // var_dump($goods);die;
		$list=[];
		foreach($goods as $k=>$v){
			$list[$k]=$v;

		}
		$list=$this->handle_img($list,'image',100,100);

		return ['code'=>400,'msg'=>'请求数据成功','data'=>['list'=>$list]];

   
    }
//兑换商品购物车结算
    public function ex_shop(){

    	if (request()->isPost()) {
			$data = input('post.');
			$cart_id =explode(',',$data['cart_id']);
			$list = array();
			foreach ($cart_id as $key => $v) {
				 $list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.pay_points,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>$data['uid'],'a.cart_id'=>$v])->find();
				 if (!$list[$key]) {
					 return ['code'=>404,'msg'=>'购物车不存在','data'=>''];
				 }
			}
			$lis=$this->handle_img($list,'image',100,100);
			return ['code'=>400,'msg'=>'获取数据成功','data'=>$lis];
		}

    }
	//加入购物车
	public function add()
    {    
		
		if(request()->isPost()){
			$param=$this->getData();
			$param['quantity']=1;
			$param['create_time']=date('Y-m-d,H:i:s');
			$check = Db::name('cart')->where('goods_id',$param['goods_id'])->find();
			if ($check['goods_id']) {
				Db::name('cart')->where('cart_id',$check['cart_id'])->setInc('quantity',1);
			}else{
				Db::name('cart')->insert($param);	
			}

			return ['code'=>400,'msg'=>'加入成功','data'=>''];
		}
		
		
	
    }
    public function numb(){
		$cart=new CartItem();
		$cart->save(['quantity' => input('post.id')],['cart_id'=>input('post.cart_id')]);
		// return true;
		return ['code'=>400,'msg'=>'修改成功','data'=>''];
	}
	public function remove(){


			$cart_item = CartItem::get(['cart_id'=>(int)input('param.cart_id')]);
			if($cart_item)
			{
				$cart_item -> delete();
				return ['code'=>400,'msg'=>'删除成功','data'=>''];
			}else{
				return ['code'=>404,'msg'=>'删除失败','data'=>''];

					

			}
		
			
	}


	//购物车结算
	public function order(){
		if (request()->isPost()) {
			$data = input('post.');
			$cart_id =explode(',',$data['cart_id']);
			$list = array();
			foreach ($cart_id as $key => $v) {
				$list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>$data['uid'],'a.cart_id'=>$v])->find();
				if (!$list[$key]) {
					return ['code'=>405,'msg'=>'购物车不存在'];
				}
			}
			$lis=$this->handle_img($list,'image',100,100);
			return ['code'=>400,'msg'=>'获取数据成功','data'=>$lis];
		}else{
			return ['code'=>405,'msg'=>'获取数据失败'];
		}



	}
//产品入会立即购买
	public function member_shop(){
		if (request()->isPost()) {
			$data = input('post.');

			$list=Db::name('goods')->where('goods_id',$data['goods_id'])->find();
			if($list['is_points_goods']==0 ){
				if ($list['end_time']!==NULL) {

					if ( strtotime($list['end_time'])<time() ) {
						$list['end_time']=1;

					}
				}
			}
			$lis=$this->handle_img($list,'image',100,100);
			return ['code'=>400,'msg'=>'获取数据成功','data'=>$lis];
		}
	}


	public function ticketTime(){
		$order = Db::name('goods_ticket')->where('uid',input('post.uid'))->where('start_time','>',strtotime("-1years",time()))->select();
		foreach ($order as $key => $val) {

			$order[$key]['start_time']=date('Y-m-d H:i:s',strtotime("1months",$val['start_time']));
			$order[$key]['end_time']=date('Y-m-d H:i:s',strtotime("1years",$val['start_time']));
			if ($val['category_id']==28) {
				$val['cash_point']=$val['cash_points'];

			}
			$order[$key]['total_points']=$val['cash_point']+$val['none_points'];
		}
		$member=Member::get(input('post.uid'));
		return ['code'=>400,'msg'=>'获取数据成功','data'=>['member'=>$member,'order'=>$order]];

	}
	
}
