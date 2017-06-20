<?php
/**
 *
 * @author    深圳韦恩斯科技有限公司
 *
 */
namespace osc\common\service;
use osc\common\model\Member;
use think\Db;
use think\Session;
use osc\member\service\User;
use osc\common\model\Address;
use osc\common\model\Cart;
use osc\common\model\Goods;
use osc\common\model\Order as OrderModel;

class Order{

	//加入订单历史
	public function add_order_history($order_id, $data=array()) {

		$order['order_id']=$order_id;
		$order['date_modified']=time();
		$order['order_status_id']=$data['order_status_id'];
//dump($order);die;
		$result = Db::name('Order')->update($order);

		$oh['order_id']=$order_id;
		$oh['order_status_id']=$data['order_status_id'];
		$oh['notify']=(isset($data['notify']) ? (int)$data['notify'] : 0) ;
		$oh['comment']=strip_tags($data['comment']);
		$oh['date_added']=time();
		$oh_id=Db::name('order_history')->insert($oh,false,true);

		return $oh_id;

	}
	//取得订单历史
	public function get_order_histories($order_id,$uid=null) {

		$map['o.order_id']=['eq',$order_id];

		if($uid){
			$map['o.uid']=['eq',$uid];
		}

		$order=Db::name('order')
				->alias('o')
				->join('order_history oh','oh.order_id = o.order_id','left')
				->join('order_status os','oh.order_status_id = os.order_status_id','left')
				->field('oh.*,os.name as order_status_name')
				->where($map)
				->order('oh.order_history_id desc')
				->select();

		return $order;
	}
	//删除订单
	public function del_order($id){

		Db::name('order')->where(array('order_id'=>$id))->delete();
		Db::name('order_goods')->where(array('order_id'=>$id))->delete();
		Db::name('order_history')->where(array('order_id'=>$id))->delete();

	}
	//取消订单
	public function cancel_order($order_id,$uid=null){

		if($uid){
			$map['uid']=['eq',$uid];
		}
		$order['order_status_id']=config('cancel_order_status_id');
		$order['date_modified']=time();
		$map['order_id']=['eq',$order_id];
		//设置订单状态	
		Db::name('order')->where($map)->update($order);

	}
	function getClientIP()  
	{  
    global $ip;  
    if (getenv("HTTP_CLIENT_IP"))  
        $ip = getenv("HTTP_CLIENT_IP");  
    else if(getenv("HTTP_X_FORWARDED_FOR"))  
        $ip = getenv("HTTP_X_FORWARDED_FOR");  
    else if(getenv("REMOTE_ADDR"))  
        $ip = getenv("REMOTE_ADDR");  
    else $ip = "Unknow";  
    return $ip;  
	} 
//购物车生成订单
	public function order_ads ($address,$arr,$uid){
		$data['shipping_name'] = isset($address['name'])?$address['name']:'';
		$data['shipping_province_id'] =isset($address['province_id'])?$address['province_id']:'';
		$data['shipping_city_id'] =isset($address['city_id'])?$address['city_id']:'';
		$data['shipping_country_id'] =isset($address['country_id'])?$address['country_id']:'';
		$data['shipping_tel'] = isset($address['telephone'])?$address['telephone']:'';
		$data['address'] = isset($address['address'])?$address['address']:'';
		$data['shipping_method'] = isset($address['uid'])?$address['uid']:'';
		$data['pay_subject']=isset($arr['pay_subject'])?$data['pay_subject']:'';
		$data['order_status_id'] = config('default_order_status_id');
		if ($arr['is_points_goods']==0) {
			$data['total']   = $arr['total'];
		}else{
			$data['pay_points']   = $arr['total'];
		}
		$data['points_order']   = $arr['is_points_goods'];
		$data['ip'] = $this->getClientIP();
		$mem=Member::get($uid);
		$data['name'] = $mem['nickname'];
		$data['tel'] = $mem['username'];
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$data['comment']   = $arr['comment'];
		$data['order_num_alias']   = build_order_no().$uid;
		$data['uid']   = $uid;
		$data['date_added']   = time();

		$order= new OrderModel();

		$order->save($data);
		$ord  = Db::name('order')->order('order_id desc')->where('uid',$uid)->find();
		$cart_id = $arr['cart_id'];

		$cart = new Cart();
		foreach ($cart_id as $key => $v) {
				$car = Cart::get($v);
				if (!$car) {
					$this->show('405','购物车不存在');die;
				}
				$da['goods_id'] = $car->goods_id;
				$da['order_id'] = $ord['order_id'];
				$goods = $car->goods()->where('goods_id',$da['goods_id'])->find();
				if ($arr['is_points_goods']==0) {
					$points=$goods['points']*$car->quantity;
					Db::name('order')->where('order_id',$ord['order_id'])->setInc('return_points',$points);
					
					$da['price'] = $goods['price'];
					$da['quantity'] = $car->quantity;
					$da['total'] = $goods['price']*$da['quantity'];	
				}else{
					$da['point'] = $goods['pay_points'];
					$da['quantity'] = $car->quantity;
					$da['points'] = $goods['pay_points']*$da['quantity'];
				}
				$da['name'] = $goods['name'];
				$order->OrderGoods()->save($da);
				Cart::destroy($v);
		}

		return [
				'order_id'=>$ord['order_id'],
				'subject'=>$ord['pay_subject'],
				'name'=>$ord['shipping_name'],//收货人姓名
				'pay_order_no'=>$ord['order_num_alias'],
				'pay_total'=>$ord['total'],
				'uid'=>$ord['uid']
		];


	}


	public function  show($code,$msg='',$data=''){
		$a=['code'=>$code,'msg'=>$msg,'data'=>$data];
		$b=json_encode($a);

		echo $b;
	}

//立即购买生成订单
	public function order_now ($address,$arr,$uid){
			$data['shipping_name'] = isset($address['name'])?$address['name']:'';
			$data['shipping_province_id'] =isset($address['province_id'])?$address['province_id']:'';
			$data['shipping_city_id'] =isset($address['city_id'])?$address['city_id']:'';
			$data['shipping_country_id'] =isset($address['country_id'])?$address['country_id']:'';
			$data['shipping_tel'] = isset($address['telephone'])?$address['telephone']:'';
			$data['shipping_method'] = isset($address['uid'])?$address['uid']:'';
			$data['address'] = isset($address['address'])?$address['address']:'';
			$data['order_status_id'] = config('default_order_status_id');
			$data['pay_subject']=isset($arr['pay_subject'])?$data['pay_subject']:'';
			if ($arr['is_points_goods']==0) {
				$data['total']   = $arr['total'];
			}else{
				$data['pay_points']   = $arr['total'];
			}
			$data['ip'] = $this->getClientIP();
		$mem=Member::get($uid);
		$data['name'] = $mem['nickname'];
		$data['tel'] = $mem['username'];
			$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];;
			$data['points_order']   = $arr['is_points_goods'];
			$data['comment']   = $arr['comment'];
			$data['order_num_alias']   = build_order_no().$uid;
			$data['uid']   = $uid;
			$data['date_added']   = time();
			$order= new OrderModel($data);
			$order->save();
			$goods = Db::name('goods')->where('goods_id',$arr['goods_id'])->find();
			$ord  = Db::name('order')->order('order_id desc')->where('uid',$uid)->find();
					$da['goods_id'] = $goods['goods_id'];
					if ($arr['is_points_goods']==0) {
						Db::name('order')->where('order_id',$ord['order_id'])->setInc('return_points',$goods['points']);
						$da['price'] = $goods['price'];
					
						$da['quantity'] = $arr['quantity'];
						$da['total'] = $goods['price']*$arr['quantity'];	
					}else{
						
						$da['point'] = $goods['pay_points'];
						$da['quantity'] = $arr['quantity'];
						$da['points'] = $goods['pay_points']*$arr['quantity'];
					}
					$da['name'] = $goods['name'];
					$order->OrderGoods()->save($da);

			return [
				'order_id'=>$ord['order_id'],
				'subject'=>$ord['pay_subject'],
				'name'=>$ord['shipping_name'],//收货人姓名
				'pay_order_no'=>$ord['order_num_alias'],
				'pay_total'=>$ord['total'],
				'uid'=>$ord['uid']
			];
		}

		


	

	//订单信息
	public function order_info($order_id,$uid=null){

		$map['o.order_id']=['eq',$order_id];

		if($uid){
			$map['m.uid']=['eq',$uid];
		}

		$order=Db::name('order')
				->alias('o')
				->join('member m','o.uid = m.uid','left')
				->join('order_status os','o.order_status_id = os.order_status_id','left')
				->join('order_history oh','oh.order_id = o.order_id','left')
				->field('o.*,oh.comment as hcomment,oh.date_added as date,m.nickname,os.name as order_status_name')
				->where($map)
				->find();
// dump($order);die;
		if(!$order){
			return false;
		}

		return array(
				'order'=>$order,
				'order_product'=>Db::name('order_goods')->alias('og')
						->join('goods g','og.goods_id = g.goods_id','left')
						->field('og.*,g.image')->where('og.order_id',$order_id)->select(),
			//'order_history'=>Db::name('order_history')->where('order_id',$order_id)->select()
				'order_history'=>$this->get_order_histories($order_id)
		);

	}
	//订单列表
	public function order_list($param=array(),$page_num=20,$uid=null){

		$query=[];

		if(isset($param['order_num'])){
			$map['o.order_num_alias']=['eq',$param['order_num']];
			$query['order_num']=urlencode($param['order_num']);
		}

		/* 微信用户查的是nick_name 不是username  */
//		if(isset($param['username'])){
//			$map['Member.username']=['like',"%".$param['username']."%"];
//			$query['username']=urlencode($param['username']);
//		}

		if(isset($param['username'])){
			$map['Member.nickname']=['like',"%".$param['username']."%"];
			$query['username']=urlencode($param['username']);
		}

		if(isset($param['status'])){
			$map['o.order_status_id']=['eq',$param['status']];
			$query['status']=urlencode($param['status']);
		}
		if(isset($param['payment_code'])){
			$map['o.payment_code']=['eq',$param['payment_code']];
			$query['payment_code']=urlencode($param['payment_code']);
		}
		if(isset($param['is_points_goods'])){
			$map['o.points_order']=['eq',$param['is_points_goods']];
			$query['is_points_goods']=urlencode($param['is_points_goods']);
		}
		if($uid){
			$map['Member.uid']=['eq',$uid];
		}
		$map['o.order_id']=['gt',0];
	// dump($map);die;
		return Db::view(['Order','o'],'*')
				->view('Member','username,reg_type,nickname','o.uid=Member.uid')
				->view('OrderStatus','order_status_id,name','o.order_status_id=OrderStatus.order_status_id')
				->where($map)
				->order('o.order_id desc')
				->paginate($page_num,false,['query'=>$query]);
	}



	//订单列表
	public function order_li($param=array(),$uid){

		$query=[];

		if(isset($param['order_num'])){
			$map['o.order_num_alias']=['eq',$param['order_num']];
			$query['order_num']=urlencode($param['order_num']);
		}

		/* 微信用户查的是nick_name 不是username  */
//		if(isset($param['username'])){
//			$map['Member.username']=['like',"%".$param['username']."%"];
//			$query['username']=urlencode($param['username']);
//		}

		if(isset($param['username'])){
			$map['Member.nickname']=['like',"%".$param['username']."%"];
			$query['username']=urlencode($param['username']);
		}

		if(isset($param['status'])){
			$map['o.order_status_id']=['eq',$param['status']];
			$query['status']=urlencode($param['status']);
		}
		if(isset($param['payment_code'])){
			$map['o.payment_code']=['eq',$param['payment_code']];
			$query['payment_code']=urlencode($param['payment_code']);
		}
		if(isset($param['is_points_goods'])){
			$map['o.points_order']=['eq',$param['is_points_goods']];
			$query['is_points_goods']=urlencode($param['is_points_goods']);
		}
		if($uid){
			$map['Member.uid']=['eq',$uid];
		}
		$map['o.order_id']=['gt',0];
		// dump($map);die;
		return Db::view(['Order','o'],'*')
				->view('Member','username,reg_type,nickname','o.uid=Member.uid')
				->view('OrderStatus','order_status_id,name','o.order_status_id=OrderStatus.order_status_id')
				->where($map)
				->order('o.order_id desc')
				->select();
	}
	/**
	 * 写人订单
	 * @param $order_data 订单数据
	 * @return array
	 */
	public function add_order($order_data=array()) {




		$order['order_type'] = isset($order_data['order_type'])&&$order_data['order_type']?$order_data['order_type']:0;

		$data=$this->get_order_data($order_data);

		$user = Member::get($data['uid']);
		if(!$user) {
			return false;
		}
		$order['uid'] = $data['uid'];
		$order['name'] = $user->username;
		$order['email'] = $user->email;
		$order['tel'] = $user->telephone?$user->telephone:'';

		$order['shipping_method']=$data['shipping_method'];

		if(!empty($data['address_id'])){
			$address = Address::get($data['address_id']);
			$order['address'] = $address->address;
			$order['area'] = $address->area;
			$order['shipping_name'] = $address->name;
			$order['shipping_tel'] = $address->telephone;
			$order['shipping_province_id'] = $address->province_id;
			$order['shipping_city_id'] = $address->city_id;
			$order['shipping_country_id'] = $address->country_id;
		}
		else {
			$order['address'] = '';
			$order['shipping_name'] = '';
			$order['shipping_tel'] = '';
			$order['shipping_province_id'] = '';
			$order['shipping_city_id'] = '';
			$order['shipping_country_id'] = '';
		}



		$order['comment']=$data['comment'];
		$order['points_order'] = $data['points_order'];


		/* 不知道为什么程序会有时候拿不到值，拿到的是null，暂时先这样让程序通过 */
		$order['order_status_id']=config('default_order_status_id')?config('default_order_status_id'):3;


//		若是定制订单，刚状态为待审核6
		if($order['order_type']==2){
			$order['order_status_id']=config('tocheck_order_status_id')?config('tocheck_order_status_id'):6;
		}

		$order['ip']=get_client_ip();

		$order['date_added'] =time();
		$order['sub_total'] = $data['sub_total'];
		$order['shipping_fee'] = $data['shipping_fee'];
		$order['total'] =$data['total'];

//		若是为拍卖商品，则价格会传过来
		if(isset($order_data['auc_total'])&&$order_data['auc_total']) {
			$order['total'] = $order_data['auc_total'];
		}
		$order['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		$order['payment_code']=$data['payment_method'];

		$order['pay_subject']=isset($data['pay_subject'])?$data['pay_subject']:'';
		$order['return_points']=isset($data['return_points'])?$data['return_points']:'';

		$order['order_num_alias'] = build_order_no();

		$order_id=Db::name('Order')->insert($order,false,true);

		if(isset($data['goodss'])){
			foreach ($data['goodss'] as $goods) {

				$goods_id=$goods['goods_id'];

				$order_goods_id=Db::execute("INSERT INTO ".config('database.prefix')."order_goods SET order_id = '" .$order_id
						."',goods_id='".$goods_id."'"
						.",name='".$goods['name']."'"
						.",model='".$goods['model']."'"
						.",quantity='".(int)$goods['quantity']."'"
						.",price='".(float)$goods['price']."'"
						.",total='".(float)$goods['total']."'"
						,[],
						true
				);


				foreach ($goods['option'] as $option) {
					Db::execute("INSERT INTO ".config('database.prefix')."order_option SET order_id = '" .$order_id
							."',order_goods_id='".$order_goods_id."'"
							.",goods_id='".(int)$option['goods_id']."'"
							.",option_id='".(int)$option['option_id']."'"
							.",option_value_id='".(int)$option['option_value_id']."'"
							.",name='".$option['name']."'"
							.",value='".$option['value']."'"
							.",type='".$option['type']."'"
					);
				}
				//支付成功后扣除库存

			}
		}


		$oh['order_id']=$order_id;
		$oh['order_status_id']=$order['order_status_id'];
		$oh['comment']=$data['comment'];
		$oh['date_added']=time();

		//		若是定制订单，刚状态为待审核6
		if($order['order_type']==2){
			$order['order_status_id']=config('tocheck_order_status_id')?config('tocheck_order_status_id'):6;
		}

		$oh_id=Db::name('OrderHistory')->insert($oh);

		//osc_order()->clear_cart($order['uid']);


		return [
				'order_id'=>$order_id,
				'subject'=>$order['pay_subject'],
				'name'=>$order['shipping_name'],//收货人姓名
				'pay_order_no'=>$order['order_num_alias'],
				'pay_total'=>$order['total'],
				'uid'=>$order['uid']
		];

	}

	private function get_order_data($param=array()){

		if(empty($param)){
			$shipping_address_id=(int)session('shipping_address_id');//送货地址
			$shipping_method=session('shipping_method');//送货方式
			$payment_method=session('payment_method');//支付方式
			$shipping_city_id=(int)session('shipping_city_id');//配送的城市，到市级地址
			$comment=session('comment');//留言
			$uid=(int)member('uid');
		}else{
			$shipping_address_id=(int)$param['shipping_address_id'];
			$shipping_method=$param['shipping_method'];
			$payment_method=$param['payment_method'];
			$shipping_city_id=(int)$param['shipping_city_id'];
			$comment=$param['comment'];
			$uid=(int)$param['uid'];
		}

		if(isset($param['type'])){
			$type=$param['type'];
		}else{
			$type='money';
		}

		$data['uid'] = $uid;
		$data['address_id']=empty($shipping_address_id)?'':$shipping_address_id;
		$data['shipping_method']=empty($shipping_method)?'':$shipping_method;

		$data['payment_method']=$payment_method;

		$data['comment']=empty($comment)?'':$comment;

		$user = Member::get($uid);
		$shop_cart = $user->carts($type);
		$cart_items = $shop_cart->get_items();

		if(!empty($cart_items)){
			//运费
			$transport_fee = $shop_cart->calculate_shipping_fee($shipping_method,$shipping_city_id);

			foreach ($cart_items as $item) {

				$option_data = array();

				foreach ($item->goods_option_objects() as $option) {

					$option_data[] = array(
							'goods_id'	  			  => $option->goods_id,
							'option_id'               => $option->option_id,
							'option_value_id'         => $option->option_value_id,
							'name'                    => $option->goodsOption->option_name,
							'value'                   => $option->optionValue->value_name,
							'type'                    => $option->goodsOption->type
					);
				}

				$goods_data[] = array(
						'goods_id'   => $item->goods_id,
						'name'       => $item->goods->name,
						'model'      => $item->goods->model,
						'option'     => $option_data,
						'quantity'   => $item->quantity,
						'subtract'   => $item->goods->subtract,
						'price'      => $item->calculate_price(),
						'total'      => $item->calculate_total_price()
				);

			}
			if(count($cart_items)>1){
				$subject = $cart_items[0]->goods->name.'等商品';
			}else{
				$subject = $cart_items[0]->goods->name;
			}
			$data['pay_subject']=$subject;

			if($type=='points'){//积分兑换的
				$data['total'] = 0;
				$data['pay_points'] = $shop_cart->count_pay_points();
				$data['sub_total'] = 0;
				$data['shipping_fee'] = 0;
				$data['points_order']=1;

			}elseif($type=='money'){//在线支付的
				$data['total'] = $shop_cart->calculate_subtotal() + $transport_fee;
				$data['return_points'] = $shop_cart->count_return_poionts();//可得积分
				$data['sub_total'] = $shop_cart->calculate_subtotal();
				$data['shipping_fee'] = $transport_fee;
				$data['points_order'] = 0;
			}

			$data['goodss'] = $goods_data;


			return $data;
		}
	}

	//更新订单，订单历史，积分，商品数量
	public function update_order($order_id){

		$order_info=Db::name('order')->where('order_id',$order_id)->find();

		$order['order_id']=$order_id;
		$order['order_status_id']=config('paid_order_status_id');
		$order['date_modified']=time();
		$order['pay_time']=time();
		Db::name('order')->update($order);
		$list=Db::name('goods')
				->alias('g')
				->join('order_goods og','g.goods_id = og.goods_id','left')
				->join('order_option oo','og.order_goods_id = oo.order_goods_id','left')
				->field('oo.*,g.*,og.quantity as goods_quantity')
				->where('og.order_id',$order_id)
				->select();

		//更新商品数量
		foreach ($list as $k => $v) {
			//存在选项
			if($v['order_option_id']){
				if($v['subtract']){//需要扣减库存

					$map['goods_id']=['eq',$v['goods_id']];
					$map['option_id']=['eq',$v['option_id']];
					$map['option_value_id']=['eq',$v['option_value_id']];

					Db::name('goods_option_value')->where($map)->setDec('quantity',$v['goods_quantity']);
					Db::name('goods')->where('goods_id',$v['goods_id'])->setDec('quantity',$v['goods_quantity']);
				}
				//不存在选项
			}else{
				//需要扣减库存
				if($v['subtract'])
					Db::name('goods')->where('goods_id',$v['goods_id'])->setDec('quantity',$v['goods_quantity']);
			}
		}


	}
	//清空购物车，用于电脑端
	public function clear_cart($uid,$type='money'){
		Db::name('cart')->where(array('uid'=>$uid,'type'=>$type))->delete();
		session('shipping_address_id',null);
		session('shipping_city_id',null);
		session('shipping_name',null);
		session('shipping_method',null);
		session('comment',null);
		session('payment_method',null);
	}
	//会员中心点击立即支付，验证商品数量
	public function check_goods_quantity($order_id){
		$goods_list=Db::view('OrderGoods','name,quantity as order_quantity')
				->view('Goods','quantity as goods_quantity','Goods.goods_id=OrderGoods.goods_id')
				->where('order_id',$order_id)->select();
		foreach ($goods_list as $k => $v) {
			if($v['order_quantity']>$v['goods_quantity']){
				return ['error'=>$v['name'].',数量不足，剩余'.$v['goods_quantity']];
			}
		}

	}


//	商品下单
	static public function make_order($user,$goods_id,$price,$quantity=1,$payment_code='weixin',$comment='',$order_type=1){

		/* 艺拍项目先清空购物车再加，以保证购物车里只有一个 开始*/
		$cart = Cart::all(['uid'=>$user->uid]);
		foreach($cart as  $v){
			$v->delete();
		}
		/*艺拍项目先清空购物车再加，以保证购物车里只有一个 结束*/


//先添加购物车
		$cart=osc_cart();
		$param = [
				'quantity'=>$quantity,
				'goods_id'=>$goods_id,
				'type'=>'money',
				'uid'=>$user->uid
		];
		$result = $cart->add($param);

		$address = Address::get(['address_id'=>$user->address_id]);

		$order = [
				'shipping_method'=>1,
				'shipping_address_id'=>$user->address_id,
				'payment_method'=>$payment_code,
				'shipping_city_id'=>$address?$address->city_id:1,
				'comment'=>$comment,
				'uid'=>$user->uid,
				'order_type'=>$order_type,
				'auc_total'=>$price
		];


		return osc_order()->add_order($order);

	}





//	获得用户不同订单状态的数量
	static public function  get_order_num($user,$order_type=0){
		$order_num = [];
		if($order_type==2){
//取定制商品数量
			$order_num['default'] = OrderModel::Where('order_status_id='.config('default_order_status_id').' and order_type=2 and uid='.$user->uid)->count();
			$order_num['paid'] = OrderModel::Where('order_status_id='.config('paid_order_status_id').' and order_type=2  and uid='.$user->uid)->count();
			$order_num['shipped'] = OrderModel::Where('order_status_id='.config('shipped_order_status_id').' and order_type=2  and uid='.$user->uid)->count();
			$order_num['tocheck'] = OrderModel::Where('order_status_id='.config('tocheck_order_status_id').' and order_type=2  and uid='.$user->uid)->count();
		}else{
//			取拍卖商品和普通商品
			$order_num['default'] = OrderModel::Where('order_status_id='.config('default_order_status_id').' and order_type in (0,1) and uid='.$user->uid)->count();
			$order_num['paid'] = OrderModel::Where('order_status_id='.config('paid_order_status_id').' and order_type in (0,1)  and uid='.$user->uid)->count();
			$order_num['shipped'] = OrderModel::Where('order_status_id='.config('shipped_order_status_id').' and order_type in (0,1)  and uid='.$user->uid)->count();
		}
		return $order_num;
	}

	public function handle_point($order_id,$uid){

		$goods=Db::name('order_goods')->where('order_id',$order_id)->select();
//dump($goods);die;
		foreach($goods as $k =>$v){

			$good =Goods::get($v['goods_id']);

			for( $i=1; $i<=$v['quantity'];$i++){
				$data['uid']=$uid;
				$data['pid']=$v['goods_id'];
				$data['start_time']=time();
				$data['return_time']=time();
				$data['cash_points']=1;

				$data['none_points']=$good['points']-1;
				$data['goods_name']=$good['name'];
				$data['category_id'] = $good->goodsToCategory->category_id;

				Db::name('goods_ticket')->insert($data);
				$this->agent_goods($uid,$good['points']);
			}




		}
		$num=count($goods);
		$cash_points =1*$num;
		Db::name('member')->where('uid',$uid)->setInc('cash_points',$cash_points);
		$order = OrderModel::get($order_id);
		Db::name('member')->where('uid',$uid)->setInc('points',$order['return_points']-$cash_points);

	}
//分销推送

	public function  get_agent($uid,$agent=300,$num = 0,$arr = array()){
		$list =  Db::name('member')->where('uid',$uid)->find();
		if($list['pid']!=NUll){
			$arr[$num]	=$list;
			$member = Db::name('member')->where('uid',$list['pid'])->find();
			$arr[$num]['phone']= $member['telephone'];
			$arr[$num]['agent']=$agent;

		}


		$num++;
		if($num==1){
			$agent=150;
		}
		if($num==2){
			$agent=80;
		}



		if($num<3){

			$arr=$this->get_agent($list['pid'],$agent,$num,$arr);

		}
		return $arr;










	}
	//处里推荐人奖励
	public function agent_goods($uid,$points){
		$arr =$this->get_agent($uid);
//dump($arr);die;
		$data=[];

		foreach($arr as $k =>$v){
			$data['uid']=$v['uid'];
			$data['pid']=$v['pid'];
			$data['create_time']=time();

			$data['phone'] = $v['phone'];
			if($points==12){
				$data['type'] = 'A1';
				$data['agent'] = $v['agent'];

			}
			if($points==52){
				$data['type'] = 'A2';
				$data['agent'] = $v['agent']*2;
			}

			$id=Db::name('trader')->insertGetId($data);
			$this->send_sms($id);
		}
	}

	//发送通知消息
	public  function  send_sms($id){
			$agent=Db::name('trader')->where('id',$id)->find();


			import('phone/ChuanglanSmsApi', EXTEND_PATH);
			$clapi = new \ChuanglanSmsApi();
			$content ='您推荐的会员购买了类型为'.$agent['type'].'的入会产品,您获得产品礼包灌装辣木籽'.$agent['agent'].'克。';
			 $clapi->sendSMS($agent['phone'],$content);

	}




}

