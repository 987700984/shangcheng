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
namespace oscshop;
use think\Db;
use osc\common\model\Member;

class Cart{
	
	
	/**
	 * 取得会员购物车商品
	 * $uid 会员id
	 * $type 支付类型，money在线付款，points积分兑换
	 */
	public function get_all_goods($uid,$type='money') {
		
		$goods_data = array();
		
		if(!$uid){
			return $goods_data;
		}

		$user = Member::get($uid);
		
		$cart_list=$user->carts()->get_items();
		
		if(!empty($cart_list)){
			
			foreach ($cart_list as $cart) {

				if($cart->goods){
					$option_data = array();

					$goods_options = $cart->goods_option_objects();
					foreach ($goods_options as $option_value_object) {

						$option_data[] = array(
							'goods_option_id'         => $option_value_object->goods_option_id,
							'goods_option_value_id'   => $option_value_object->goods_option_value_id,
							'option_id'               => $option_value_object->option_id,
							'option_value_id'         => $option_value_object->option_value_id,
							'name'                    => $option_value_object->goodsOption->option_name,
							'value'            		  => $option_value_object->optionValue->value_name,
							'type'                    => $option_value_object->goodsOption->type,
							'quantity'                => $option_value_object->quantity,
							'subtract'                => $option_value_object->subtract,
							'price'                   => $option_value_object->price,
							'price_prefix'            => $option_value_object->price_prefix,
							'weight'                  => $option_value_object->weight,
							'weight_prefix'           => $option_value_object->weight_prefix
						);

						 
					}

					
					$goods_data[] = array(
						'cart_id'                   => $cart->cart_id,
						'goods_id'                  => $cart->goods_id,
						'name'                      => $cart->goods->name,
						'model'                     => $cart->goods->model,
						'shipping'                  => $cart->goods->shipping,
						'image'                     => resize($cart->goods->image,80,80),
						'quantity'                  => $cart->quantity,
						'minimum'                   => $cart->goods->minimum,
						'subtract'                  => $cart->goods->subtract,
						'price'                     => $cart->calculate_price(),
						'origin_price'              =>$cart->goods->origin_price,
						'is_points_goods'           =>$cart->goods->is_points_goods,
						'end_time'                  =>$cart->goods->end_time,
						'total'                     => $cart->calculate_total_price(),
						'pay_points'                => $cart->goods->pay_points,
						'total_pay_points'          => $cart->get_total_pay_points(),
						'total_return_points'       => $cart->get_total_return_points(),
						'weight'          			=> $cart->calculate_total_weight(),
						'weight_class_id'           => $cart->goods->weight_class_id,
						'length'                    => $cart->goods->length,
						'width'                     => $cart->goods->width,
						'height'                    => $cart->goods->height,
						'length_class_id'           => $cart->goods->length_class_id,
						'stock'                     => $cart->has_enough_stock(),
						'option'                    => $option_data,
					);


				}
			}			
		}
		return $goods_data;
	}

	
	/**
	 * 加入购物车
	 *@param uid 	       用户id
	 *@param goods_id  商品id
	 *@param quantity  商品数量 
	 *@param option    商品选项 
	 */
	public function add($data=array()){
		/*
		 * Array
		(
			[option] => Array
				(
					[8,1] => 1
					[8,3] => 6
				)

			[quantity] => 1
			[goods_id] => 8
			[uid] => 1
		)
		 */
		
		if(empty($data)){
			return false;
		}
		
		$cart['uid']=$data['uid'];
		$cart['goods_id']=(int)$data['goods_id'];
		$cart['quantity']=(int)$data['quantity'];
		
		if(isset($data['type'])){
			$cart['type']=$data['type'];
		}

		$cart['goods_option']= isset($data['option'])?json_encode(array_filter($data['option'])):'';
		
		$cart['create_time']=time();
	
		if($cart_id=Db::name('cart')->insert($cart,false,true)){
			return true;
		}else{
			return false;
		}
	}

	
	
	/**
	 * 判断商品是否存在，并验证最小起订量
	 *@param goods_id  商品id
	 *@param quantity  商品数量 
	 */
	public function check_minimum($param=array()){
		
		if(empty($param)){
			return false;
		}
		
		if($goods=Db::name('goods')->find((int)$param['goods_id'])){			
			if((int)$param['quantity']<$goods['minimum']){
   				return ['error'=>'最小起订量是'.$goods['minimum'],'minimum'=>$goods['minimum']];
   			} 			
		}else{
			return ['error'=>'商品不存在'];
		}
	}
	

	
	/**
	 * 验证商品数量和必选项
	 *@param $param['goods_id']
	 *@param $param['quantity']
	 *@param $param['option']
	 */
	public function check_quantity($param=array()){		
		
		$goods_id=(int)$param['goods_id'];
		$quantity=(int)$param['quantity'];
		
		if (!isset($param['option'])) {		
			$param['option'] =[];	
		}

		$option_query = osc_goods()->get_goods_options($goods_id);
		$option = [];

		foreach ($option_query as $k => $v) {
			foreach ($v['goods_option_value'] as $k1 => $v1) {
				$option[$goods_id.','.$v['option_id']]['required']=$v['required'];
				$option[$goods_id.','.$v['option_id']]['name']=$v['name'];
				$option[$goods_id.','.$v['option_id']]['goods_option_id']=$v['goods_option_id'];
				$option[$goods_id.','.$v['option_id']][$v1['option_value_id']]=$v1;
				$option[$goods_id.','.$v['option_id']][$v1['option_value_id']]['type']=$v['type'];


				/*				$option[$v['goods_option_id']]['required']=$v['required'];
                                $option[$v['goods_option_id']]['name']=$v['name'];
                                $option[$v['goods_option_id']]['goods_option_id']=$v['goods_option_id'];
                                $option[$v['goods_option_id']][$v1['option_value_id']]=$v1;
                                $option[$v['goods_option_id']][$v1['option_value_id']]['type']=$v['type'];*/
			}
		}

		foreach ($option as $key=> $product_option) {			
			if ($product_option['required'] && empty($param['option'][$key])) {					
				return	['error'=> $product_option['name'].'是必选项','goods_option_id'=>$product_option['goods_option_id']];
			}			
		}		
		//存在选项的
		if(!empty($param['option'])){												
			foreach ($param['option'] as $k=>$v) {				
				if(is_array($v)){					
					foreach ($v as $k1 => $v1) {
						//需要扣减库存的要验证数量
						if($quantity>$option[$k][$v1]['quantity']&&($option[$k][$v1]['subtract']==1)){
							return ['error'=>$option[$k][$v1]['name'].'数量不足，剩余'.$option[$k][$v1]['quantity']];
						}
					}					
				}else{
					//需要扣减库存的要验证数量
					if($quantity>$option[$k][$v]['quantity']&&($option[$k][$v]['subtract']==1)){
					return ['error'=>$option[$k][$v]['name'].'数量不足，剩余'.$option[$k][$v]['quantity']];
					}
				}				
			}	
		}else{
			//不存在选项的			
			$goods=Db::name('goods')->where('goods_id',$goods_id)->find();
			
			if($quantity>$goods['quantity']&&($goods['subtract']==1)){
				return ['error'=>'数量不足，剩余'.$goods['quantity']];
			}
		}				
	
	}

	public function check($param) {
		//判断商品是否存在，并验证最小起订量
		if($error=$this->check_minimum($param)){
			return $error;
		}
		//验证商品数量和必选项
		if($error=$this->check_quantity($param)){
			return $error;
		}
	}
/**
	 * 查询某类型商品购物车
	 *@param $$filed  只查字段
	 *@param $is_points_goods 类型
	*@param $uid    会员id
	 */
	public function get_other_list($is_points_goods,$uid){
		Db::name('cart')->alias('a')->join('__GOODS__ w','a.goods_id = w.goods_id')->where(['w.is_points_goods'=>$is_points_goods,'uid'=>$uid])->paginate(20);
	}





}
