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
namespace osc\index\controller;
use osc\common\controller\HomeBase;
use think\Db;
use osc\member\service\User;
use osc\common\model\Cart as CartItem;
class Cart extends HomeBase
{
	protected function _initialize(){
		parent::_initialize();
		define('UID',User::is_login());
		
	}
		
    public function index()
    {		
		if(!UID){
			$this->error('请先登录');
			}

		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>member('uid')])->paginate(5);
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
		$this->assign('list4',$this->sell(8));
		$this->assign('page',$goods->render());
		$this->assign('SEO',['title'=>'购物车(入会商品)-'.config('SITE_URL').'-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }
 	public function ex_cart()
    {		
		if(!UID){
			$this->error('请先登录');
		}
		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.pay_points,w.origin_price,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>member('uid')])->paginate(5);
        // var_dump($goods);die;
		$this->assign('list',$goods);
		
		$this->assign('SEO',['title'=>'购物车(兑换商品)-'.config('SITE_URL').'-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		$this->assign('list4',Db::name('goods')->where(['is_points_goods'=>1,'status'=>1])->order("viewed desc")->limit(8)->select());
		return $this->fetch();
   
    }
	//购物车结算
	public function me_cart(){
		if (request()->isPost()) {
			$data = input('post.');
			$cart_id = $data['cart_id'];
			$list = array();
			foreach ($cart_id as $key => $v) {
				$list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>member('uid'),'a.cart_id'=>$v])->find();
				if (!$list[$key]) {
					$this->error('购物车不存在');
				}
				if ($list[$key]['end_time']!==NULL) {
					$list[$key]=$list[$key];
					if ( strtotime($list[$key]['end_time'])<time() ) {
						$list[$key]['end_time']=1;

					}
				}

			}
//			dump($list);die;
			$this->assign('list',$list);
			$this->assign('address',Db::name('address')->where('uid',member('uid'))->select());
			$this->assign('addres',Db::name('address')->where('uid',0)->select());
			$this->assign('SEO',['title'=>'结算 - '.config('SITE_URL').'-'.config('SITE_TITLE')]);
			return $this->fetch();
		}else{
			$this->error('参数错误！');
		}



	}
//兑换商品购物车结算
    public function ex_shop(){
    	if(!UID){
			$this->error('请先登录');
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
			$this->assign('address',Db::name('address')->where('uid',member('uid'))->select());
			$this->assign('addres',Db::name('address')->where('uid',0)->select());

			$this->assign('SEO',['title'=>'购物车结算(兑换商品)-'.config('SITE_URL').'-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
			$this->assign('list',$list);
			return $this->fetch();	
		}else{
			$this->error('参数错误！');
		}
    }
	//加入购物车
	public function add()
    {    
		
		if(!$uid=User::is_login()){
			return false;
		}else{
			$param=input('post.');
			$param['uid']=$uid;
			$param['quantity']=$param['quantity'];
			$param['create_time']=date('Y-m-d,H:i:s');
			$check = Db::name('cart')->where('goods_id',$param['goods_id'])->find();
			if ($check['goods_id']) {
				Db::name('cart')->where('cart_id',$check['cart_id'])->setInc('quantity',$param['quantity']);
			}else{
				Db::name('cart')->insert($param);	
			}
			
			return true;
		}
		
		
	
    }
//立即购买
	public function member_shop(){
		if(!UID){
			$this->error('请先登录');
		}
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
			$this->assign('quantity',$data['quantity']);
			$this->assign('address',Db::name('address')->where('uid',member('uid'))->select());
			$this->assign('addres',Db::name('address')->where('uid',0)->select());
			$this->assign('SEO',['title'=>'结算 - '.config('SITE_URL').'-'.config('SITE_TITLE')]);
			return $this->fetch();
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
