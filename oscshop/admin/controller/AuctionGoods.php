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

namespace osc\admin\controller;
use osc\common\model\AttributeValue;
use \think\Db;
use think\Request;
use osc\common\model\admin;
use osc\common\model\WeightClass;
use osc\common\model\LengthClass;
use osc\common\model\Goods;
use osc\common\model\Auctioning;
use osc\common\model\GoodsDescription;
use osc\common\model\AucGoods;
use osc\common\model\AuctionSpecialShow;
use osc\common\model\AuctionGoodsSpecialShowRelation;
use osc\common\model\GoodsImage;
use osc\common\model\GoodsAttribute;
use osc\common\model\Brand;
use osc\admin\service\User;
class AuctionGoods extends GoodsBase{
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','拍品');
		$this->assign('breadcrumb2','拍品管理');
	}

//	拍卖商品输出
	public function index(){
//		若有搜索参数就搜索
		$filter=input('param.');
		$where = 'is_auction=1';
		if(isset($filter['search_status'])&&$filter['search_status']=='1') {
			$where .= ' and status=1';
		}
		elseif(isset($filter['search_status'])&&$filter['search_status']=='2') {
			$where .= ' and status!=1';
		}
		if(isset($filter['search_goods_name'])&&$filter['search_goods_name']){
			$where .= ' and name like \'%'.$filter['search_goods_name'].'%\'';
		}

		$list = AucGoods::where($where)->order('goods_id desc')->paginate(12,false, ['query' => $filter]);
		$this->assign('list',$list);
		$this->assign('shows',AuctionSpecialShow::where('is_delete=0')->select());
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');

		return $this->fetch();
	}



	public function add(){
//添加拍品操作
		if(request()->isPost()){
			$data=input('post.');

			$result = $this->validate($data,'AuctionGoods');
			if(true !== $result) {
				$this->error($result);
			}else {
				$result = $this->validate($data['auction'],'AuctionGoodsMsg');
			}
			if(true !== $result) {
				$this->error($result);
			}else {
//				拍品表数据入库
				$AucGoods = new AucGoods($data);
				$AucGoods->allowField(true)->save();
				if(!isset($AucGoods->goods_id)){
					$this->error('数据添加失败，请重试');
				}
//				拍卖专场入库
				if(isset($data['goods_category'])&&!empty($data['goods_category'])){
					foreach($data['goods_category'] as $k=>$v){
						$AucSpecialShowRelation = new AuctionGoodsSpecialShowRelation([
								'goods_id'=>$AucGoods->goods_id,
								'special_show_id'=>$v
						]);
						$AucSpecialShowRelation->allowField(true)->save();
					}
				}

				//				拍卖商品属性入库
				if(isset($data['goods_attribute'])&&!empty($data['goods_attribute'])){
					foreach($data['goods_attribute'] as $k=>$v){
						$GoodsAttribute = new GoodsAttribute([
								'goods_id'=>$AucGoods->goods_id,
								'attribute_value_id'=>$v
						]);
						$GoodsAttribute->allowField(true)->save();
					}
				}

//			拍品图片入库
				if(isset($data['goods_image'])&&!empty($data['goods_image'])){
					foreach($data['goods_image'] as $k=>$v){
						if(!$v['image']){
							continue;
						}
						$GoodsImage = new GoodsImage([
								'goods_id'=>$AucGoods->goods_id,
								'image'=>$v['image'],
								'sort_order'=>$v['sort_order']
						]);
						$GoodsImage->allowField(true)->save();
					}
				}
//				拍卖信息数据入库
				$data['auction']['goods_id'] = $AucGoods->goods_id;


//				if(!empty($AucGoods->auctioning->AuctionSpecialShow)){
//					foreach($AucGoods->auctioning->AuctionSpecialShow as $k=>$v){
//						if($v->id>5){
//							$data['auction']['auction_begintime'] = $v->auction_begintime;
//							$data['auction']['auction_endtime'] = $v->auction_endtime;
//						}
//					}
//				}

				$Auctioning = new Auctioning($data['auction']);
				$result2 = $Auctioning->allowField(true)->save();
		$Auctioning2 = Auctioning::get(['goods_id'=>$AucGoods->goods_id]);

				if(!empty($Auctioning2->AuctionSpecialShow)){
					foreach($Auctioning2->AuctionSpecialShow as $k=>$v){
						if($v->id>5){
							$Auctioning2->auction_begintime = $v->auction_begintime;
							$Auctioning2->auction_endtime = $v->auction_endtime;
	$Auctioning2->save();
						}
					}
				}


//				拍品详情信息入库
				$data['goods_description']['goods_id'] = $AucGoods->goods_id;
				$data['goods_description']['goods_description_id'] = $AucGoods->goods_id;
				$GoodsDescription = new GoodsDescription($data['goods_description']);
				$result3 = $GoodsDescription->allowField(true)->save();
				if($result2&&$result3){
					User::get_logined_user()->storage_user_action('添加了一样拍品');

					$this->success('添加成功！');
				}else{
					$this->error('添加失败！请稍候重试!');
				}
			}
		}

//添加拍品页面
		$this->assign('weight_class',WeightClass::all());
		$this->assign('length_class',LengthClass::all());
		$this->assign('show',AuctionSpecialShow::all());
		$this->assign('brand',Brand::all());
		$this->assign('crumbs', '新增');
		return $this->fetch();
	}


	//	上线/下架状态更新
	public function set_status(){
		$Goods = Goods::get(['goods_id'=>input('id')]);
		if(input('status')&&(input('status')==2)){
			$Goods->status = 2;
		}else{
			$Goods->status = 1;
		}
		$result = $Goods->save();
		if($result){

			$this->redirect('index');
		}else{
			$this->error('审核操作失败，请重试！');
		}
	}

	//商品基本信息更改
	public function edit_general(){
		if(request()->isPost()){
			$data=input('post.');
			$result = $this->validate($data,'AuctionGoodsEditBase');
			if(true !== $result) {
				$this->error($result);
			}else {
				$AucGoods = AucGoods::get(['goods_id'=>$data['goods_id']]);
				$result = $AucGoods->allowField(true)->save($data);
				$GoodsDescription = GoodsDescription::get(['goods_id'=>$data['goods_id']]);
				if(!$GoodsDescription){
//				如果添加时数据不完整，修改时可以补加
					$GoodsDescription = new GoodsDescription();
					$data['goods_description']['goods_id'] = $data['goods_id'];
					$data['goods_description']['goods_description_id'] = $data['goods_id'];
				}
				$result1 = $GoodsDescription->allowField(true)->save($data['goods_description']);
				if($result||$result1){
					User::get_logined_user()->storage_user_action('更改了商品信息');
					$this->success('修改成功!',url('index'));
				}else{
					$this->error('您没有更新数据！');
				}
			}
		}
		$this->assign('weight_class',WeightClass::all());
		$this->assign('length_class',LengthClass::all());
		$Auctioning = Auctioning::get(['goods_id'=>input('id')]);
		if(empty($Auctioning)){
			$this->error('此条信息已经不完整，请将其删除后重新添加');
		}
		$this->assign('auctioning',$Auctioning);
		$this->assign('brand',Brand::all());
		$this->assign('crumbs', '编辑基本信息');
		return $this->fetch('general');
	}


//	商品关联信息及拍卖信息修改
	public function edit_links(){
		if(request()->isPost()){
			$data=input('post.');

			$result = $this->validate($data['auction'],'AuctionGoodsMsg');
			if(true !== $result) {
				$this->error($result);
			}else {
				//				拍卖专场入库
				if(isset($data['goods_category'])&&!empty($data['goods_category'])){
					if(!empty(AuctionGoodsSpecialShowRelation::all(['goods_id'=>$data['goods_id']]))){
						AuctionGoodsSpecialShowRelation::destroy(['goods_id'=>$data['goods_id']]);
					}
					foreach($data['goods_category'] as $k=>$v){
						$AucSpecialShowRelation = new AuctionGoodsSpecialShowRelation([
								'goods_id'=>$data['goods_id'],
								'special_show_id'=>$v
						]);
						$AucSpecialShowRelation->allowField(true)->save();
					}
				}

				//				拍卖商品属性入库
				if(isset($data['goods_attribute'])&&!empty($data['goods_attribute'])){

					if(!empty(GoodsAttribute::all(['goods_id'=>$data['goods_id']]))){
						GoodsAttribute::destroy(['goods_id'=>$data['goods_id']]);
					}
					foreach($data['goods_attribute'] as $k=>$v){
						$GoodsAttribute = new GoodsAttribute([
								'goods_id'=>$data['goods_id'],
								'attribute_value_id'=>$v
						]);
						$GoodsAttribute->allowField(true)->save();
					}
				}
				//				拍卖信息数据入库
				$data['auction']['goods_id'] = $data['goods_id'];
				$AucGoods = AucGoods::get(['goods_id'=>$data['goods_id']]);


				if(!empty($AucGoods->auctioning->AuctionSpecialShow)){
					foreach($AucGoods->auctioning->AuctionSpecialShow as $k=>$v){
						if($v->id>5){
							$data['auction']['auction_begintime'] = $v->auction_begintime;
							$data['auction']['auction_endtime'] = $v->auction_endtime;
						}
					}
				}



				$Auctioning = Auctioning::get(['goods_id'=>$data['goods_id']]);
				$result = $Auctioning->allowField(true)->save($data['auction']);
				User::get_logined_user()->storage_user_action('更改了商品信息');
				$this->success('修改成功!',url('index'));
			}
		}

		$this->assign('goods',AucGoods::get(['goods_id'=>input('id')]));
		$Auctioning = Auctioning::get(['goods_id'=>input('id')]);
		if(empty($Auctioning)){
			$this->error('此条信息已经不完整，请将其删除后重新添加');
		}
		$this->assign('auctioning',$Auctioning);
		$this->assign('crumbs', '编辑关联信息及拍卖信息');
		return $this->fetch('links');
	}

	//	商品相册修改
	public function edit_image(){

		$Auctioning = Auctioning::get(['goods_id'=>input('id')]);
		if(empty($Auctioning)){
			$this->error('此条信息已经不完整，请将其删除后重新添加');
		}

		$this->assign('goods_images',GoodsImage::where('goods_id',input('id'))->order('sort_order asc')->select());
		$this->assign('crumbs', '拍品相册');
		return $this->fetch('image');
	}

//	删除商品
	public function del(){
		if(null!==input('id')&&input('id')>0){
			$AucGoods = AucGoods::get(['goods_id'=>input('id')]);
			if($AucGoods){
				$AucGoods->auctioning->AuctionSpecialShow()->detach(['goods_id'=>input('id')]);
				AucGoods::destroy(['goods_id'=>input('id')]);
				Auctioning::destroy(['goods_id'=>input('id')]);
			}
			User::get_logined_user()->storage_user_action('删除了一件拍品');
			$this->success('删除成功!');
		}
	}
}
