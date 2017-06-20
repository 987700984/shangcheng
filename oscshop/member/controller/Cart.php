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
use think\Db;
use osc\member\service\User;
use osc\common\model\Cart as CartItem;
class Cart extends HomeBase
{
	
		
    public function index()
    {		
		
		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.origin_price,w.name,w.image,w.end_time')->where(['w.is_points_goods'=>0,'uid'=>member('uid')])->paginate(10);
//
		foreach ($goods as $key => $v) {
            if ($v['end_time']!==NULL) {

                if ( strtotime($v['end_time'])<time() ) {
                    $goods[$key]['end_time']=1;

                }
            }
        }
        // var_dump($goods);die;
		$this->assign('list',$goods);
		
		$this->assign('SEO',['title'=>'购物车(入会商品)-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }
 	public function ex_cart()
    {		
		
		$goods=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.price,w.pay_points,w.origin_price,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>member('uid')])->paginate(10);
        // var_dump($goods);die;
		$this->assign('list',$goods);
		
		$this->assign('SEO',['title'=>'购物车(兑换商品)-'.config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }
//兑换商品购物车结算
    public function ex_shop(){
    	if (request()->isPost()) {
			$data = input('post.');
			$cart_id = $data['cart_id'];
			$list = array();
			foreach ($cart_id as $key => $v) {
			 $list[$key]=Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->field('a.*,w.pay_points,w.name,w.image')->where(['w.is_points_goods'=>1,'uid'=>member('uid'),'a.cart_id'=>$v])->find();
			}
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
			$param['quantity']=1;
			$param['create_time']=date('Y-m-d,H:i:s');
			Db::name('cart')->insert($param);
			return true;
		}
		
		
	
    }
    public function numb(){
		$cart=new CartItem();
		$cart->save(['quantity' => input('post.id')],['cart_id'=>input('post.cart_id')]);
		// return true;
		return 1;
	}
	//更新购物车
	public function update(){
		
		if(!$uid=User::is_login()){
			return ['error'=>'请先登录！！'];
		}
		
		$d=input('post.');
		
		$cart=osc_cart();

		$goods_id=(int)$d['id'];	
		
		$quantity=(int)$d['q'];
		
		$cart_id=(int)$d['cart_id'];
		
		$cart_data=Db::name('cart')->find($cart_id);			
		$param['option']=json_decode($cart_data['goods_option'], true);
		$param['goods_id']=$goods_id;
		$param['quantity']=$quantity;

		if($error = $cart->check($param))
		{
			return $error;
		}
		//更新购物车商品数量		
		$cart_item = \osc\common\model\Cart::get(['cart_id' => (int)$cart_id,'uid' => $uid]);
		$cart_item->quantity = (int)$quantity;
		$cart_item->save();

		$user = User::get_logined_user();
		$shop_cart = $user->carts();


		$user->storage_user_action('更新了购物车商品');
		
		$json['success'] = $shop_cart->count_cart_total();
		//商品单价
		$json['price'] = $cart_item->calculate_price();
		//单个商品总价
		$json['total_price']= $cart_item->calculate_total_price();
		//所有商品总价
		$json['total_all_price']=$shop_cart->calculate_subtotal();
		//所有商品重量
		$json['weight']=$shop_cart->get_weight();
			
		return $json;
		
		
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
