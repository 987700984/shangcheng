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
use osc\common\model\GoodsComment;
use osc\common\model\Goods;
use osc\common\model\Admin;
use osc\common\controller\AdminBase;
use \think\Session;
class CommentBackend extends AdminBase{


//	评论数据输出
	public function index(){
//		若有搜索参数就搜索
		$filter=input('param.');
		$where = 'parent_id=0';
		if(isset($filter['search_status'])&&$filter['search_status']=='1') {
			$where .= ' and status=1';
		}
		elseif(isset($filter['search_status'])&&$filter['search_status']=='2') {
			$where .= ' and status!=1';
		}
		if(isset($filter['search_goods_id'])&&is_numeric($filter['search_goods_id'])){
			$where .= ' and goods_id='.$filter['search_goods_id'];
		}
		$list = GoodsComment::where($where)->paginate(10,false, ['query' => $filter]);
//		将评论的二级评论查询出来
		$sub_commends = array();
		foreach($list as $k=>$v){
			$sub_commends[$k] = GoodsComment::all(array('parent_id'=>$v['id']));
			$arr = [];
			foreach($sub_commends[$k] as $kk => $vv){
				$sub_commends[$k][$kk]['admin_name'] = $vv->user_name;
			}
		}
		$this->assign('list',$list);
		// var_dump($list);die;
		$this->assign('subCommends',$sub_commends);
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		return $this->fetch();
	}

//	评论审核
	public function pass(){
		$GoodsComment = GoodsComment::get(['id'=>input('id')]);
		if(input('status')&&(input('status')==2||input('status')==0)){
			$GoodsComment->status = 2;
		}else{
			$GoodsComment->status = 1;
		}
		$GoodsComment->admin_id = User::get_logined_user()['admin_id'];
		$result = $GoodsComment->save();
		if($result){

			$this->redirect('index');
		}else{
			$this->error('审核操作失败，请重试！');
		}
	}

//	回复评论
	public function answer(){
		$data = Request::instance()->param();
		$goodsComment = new GoodsComment([
				'goods_id'=>$data['goods_id'],
				'content'=>$data['content'],
				'parent_id' => $data['pid'],
				'ip_address'=> Request::instance()->ip(),
//				这三项数据在session中取
				'user_name' => User::get_logined_user()['user_name'],
				'status'=>1
		]);
		$result = $goodsComment->save();
		if($result){
			User::get_logined_user()->storage_user_action('回复了评论');
			$this->success('回复评论成功');
		}else{
			$this->error('回复评论失败！');
		}
	}

//	添加假评论
	public function addComment(){
		$data = Request::instance()->param();
		$result = $this->validate($data,'AddComment');
		if(true !== $result) {
			$this->error($result);
		}else{
			//goods_id需要验证是否存在
			$Goods = Goods::get($data['goods_id']);
			if(!$Goods){
				$this->error('商品ID不正确，请重新填写！');
			}
			$goodsComment = new GoodsComment([
					'goods_id'=>$data['goods_id'],
					'content'=>$data['content'],
					'parent_id' => 0,
					'ip_address'=> Request::instance()->ip(),
//				这三项数据在session中取
					'user_name' => $data['username'],
					'status'=>1
			]);
			$result = $goodsComment->save();
			if($result){
				User::get_logined_user()->storage_user_action('添加了一条评论');
				$this->success('添加评论成功');
			}else{
				$this->error('添加评论失败！');
			}
		}
	}
}
