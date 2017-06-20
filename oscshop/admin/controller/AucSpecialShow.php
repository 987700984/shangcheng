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
use \think\Db;
use think\Request;
use osc\common\model\GoodsComment;
use osc\common\model\AuctionSpecialShow;
use osc\common\model\Auctioning;
use osc\common\model\Admin;
use osc\common\controller\AdminBase;
use \think\Session;
use osc\admin\service\User;

class AucSpecialShow extends GoodsBase{

	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','拍品');
		$this->assign('breadcrumb2','专场管理');
	}


//	专场数据输出
	public function index(){
		$list = AuctionSpecialShow::where('is_delete=0 and id>5')->order('id desc')->paginate(12);
		$this->assign('list',$list);
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		return $this->fetch();
	}

//	添加和修改操作
	public function addAndEdit(){
		if(request()->isPost()){
			$data=input('post.');
			$result = $this->validate($data,'AuctionSpecialShow');
			if(true !== $result) {
				$this->error($result);
			}else {
				if(isset($data['id'])&&$data['id']>0){
					$AuctionSpecialShow = AuctionSpecialShow::get(['id'=>$data['id']]);
					$result = $AuctionSpecialShow->allowField(true)->save($data);
//					专场表里的所有拍品的时间也要更新

					if($AuctionSpecialShow->auctioning){
						foreach($AuctionSpecialShow->auctioning as $k => $v){
							Auctioning::where('goods_id', $v->goods_id)
									->update(['auction_begintime'=>$data['auction_begintime'],'auction_endtime'=>$data['auction_endtime']]);
						}
					}
				}else{
					$AuctionSpecialShow = new AuctionSpecialShow();

					$result = $AuctionSpecialShow->allowField(true)->save($data);

					User::get_logined_user()->storage_user_action('添加/修改了一个专场');
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
			$this->assign('show',AuctionSpecialShow::get(['id'=>input('id')]));
			$this->assign('crumbs','新增');
			return $this->fetch('edit');
		}
	}


//	删除操作
	public function del(){
		$AucSpecialShow = AuctionSpecialShow::get(['id'=>input('id')]);
		$AucSpecialShow->is_delete = 1;
		$result = $AucSpecialShow->save();
		if($result){
			User::get_logined_user()->storage_user_action('删除了一个专场');

			$this->redirect('AucSpecialShow/index');
		}else{
			$this->error('删除失败！');
		}
	}



//自动完成
	function autocomplete(){
		$filter_name=input('filter_name');
$where = '1=1';
		if (isset($filter_name)) {
			$where .= "and LIKE'%".$filter_name."%'";
		}
		$results = AuctionSpecialShow::where($where)->select();
		$json=[];
		foreach ($results as $result) {
			if(strtotime($result['auction_endtime'])>time()||$result['id']<=5){
				$json[] = array(
						'show_id' => $result['id'],
						'name'=> strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
				);
			}

		}
		return 	$json;
	}




}
