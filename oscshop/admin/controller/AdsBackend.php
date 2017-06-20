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
use osc\admin\service\User;
use \think\Db;
use think\Request;
use osc\common\model\AdsItems;
use osc\common\model\Goods;
use osc\common\model\Ads;
use osc\common\controller\AdminBase;
use \think\Session;



class AdsBackend extends AdminBase{


//	广告位数据输出
	public function index(){

		$ads = Ads::all();
		$this->assign('ads',$ads);
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		return $this->fetch();

	}


//	添加新的广告位
	public function edit_ads(){
		if(request()->isPost()) {
$admin = User::get_logined_user();
			$data=input('post.');
			$result = $this->validate($data,'EditAds');
			if(true !== $result) {
				$this->error($result);
			}else {
				if(isset($data['id'])&&$data['id']>0){
					$Ads = Ads::get(['id'=>$data['id']]);
					$data['update_user'] = $admin->user_name;
					$data['update_time'] = date('Y-m-d H:i:s',time());
					$result = $Ads->allowField(true)->save($data);
				}else{
					$Ads = new Ads();
					$data['create_user'] = $admin->user_name;
					$data['create_time'] = date('Y-m-d H:i:s',time());
					$result = $Ads->allowField(true)->save($data);

					User::get_logined_user()->storage_user_action('添加/修改了一个广告位');
				}
				if($result){
					$this->success('操作成功！','index');
				}elseif($result===0) {
					$this->error('您没有更新数据');
				}else{
					$this->error('操作失败！请稍候重试!');
				}
			}


		}else{

			$this->assign('ads',Ads::get(['id'=>input('id')]));
			return $this->fetch();
		}


	}
//  删除评论
	public function del(){
		if(request()->isGet()){
			if(Db::name('ads')->where('id',input('param.id'))->delete()){
				$this->success('删除成功','AdsBackend/index');
			}else{
				$this->error('删除失败！');
			}	
		}
		
	}

	public function edit(){
		$items = AdsItems::all(['ad_id'=>input('id')]);
		$this->assign('items',$items);
		return $this->fetch();
	}


	//编辑信息，新增，修改
	function ajax_edit(){
		if(request()->isPost()){

			$data=input('post.');
//var_dump($data);die;
			if(isset($data['id'])&&$data['id']!=''){
//				//更新
				$item = AdsItems::get(['id'=>$data['id']]);
				$item->ad_id = $data['goods_id'];
				$item->image_url = $data['goods_image'][$data['key']]['image_url'];
				$item->link_url = $data['goods_image'][$data['key']]['link_url'];
				$result = $item->save();
				if($result){
					return ['status'=>1,'success'=>'更新成功'];
				}else{
					return ['status'=>2,'error'=>'未更新'];
				}


			}else{
				//新增

				$item = new AdsItems([
						'ad_id'=>$data['goods_id'],
						'image_url'=>$data['goods_image'][$data['key']]['image_url'],
						'link_url'=>$data['goods_image'][$data['key']]['link_url']
				]);
				$result = $item->save();
				if($result){
					return ['id'=>$item->id,'success'=>'添加成功'];

				}else{
					return ['status'=>4,'error'=>'添加失败'];
				}


			}
		}
	}






}
