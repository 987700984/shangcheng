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
class Collect extends HomeBase{


	//	点击收藏和取消收藏
	public function save(){
		if(!$uid=User::is_login()){
			return ['error'=>'请先登录！！'];
		}
			$data['uid']=member('uid');
			$data['goods_id']=input('post.goods_id');
			
		switch (input('post.id')) {
			case 'add0':
				$data['is_points_goods']=0;
				Db::name('collect')->insert($data);
				break;
			case 'del0':
				$data['is_points_goods']=0;
				Db::name('collect')->where(['uid'=>$data['uid'],'goods_id'=>$data['goods_id'],'is_points_goods'=>$data['is_points_goods']])->delete();
				break;
			case 'add1':
				$data['is_points_goods']=1;
				Db::name('collect')->insert($data);
				break;
			default:
				$data['is_points_goods']=1;
				Db::name('collect')->where(['uid'=>$data['uid'],'goods_id'=>$data['goods_id'],'is_points_goods'=>$data['is_points_goods']])->delete();
				break;
		}
		
		return true;
	}
	//统计订单数量
	public function num($id){
		$count = Db::name('order')->where('order_status_id',$id)->count();
		return $count;
	}
	//	验证是否收藏
	public function save_check(){
		if(!$uid=User::is_login()){
			return ['error'=>'请先登录！！'];
		}
		$data['uid']=member('uid');
		$data['goods_id']=input('post.goods_id');
		$collect=Db::name('collect')->where(['uid'=>$data['uid'],'goods_id'=>$data['goods_id']])->find();
		if(empty($collect)){
			Db::name('collect')->insert($data);
			return ['success'=>'收藏成功'];
		}else{
			return ['success'=>'该商品已收藏'];
		}



	}
	//入会关注
	public function member_col(){
		$list = Db::name('goods')->alias('a')->field('a.*,w.id')->join('collect w','a.goods_id=w.goods_id')->where(['w.uid'=>member('uid')])->select();

		$this->assign('number',['1'=>$this->num(1),'3'=>$this->num(3),'4'=>$this->num(4)]);
		$this->assign('member',User::get_logined_user());
       $this->assign('list',$list);
		$this->assign('SEO',['title'=>'入会关注 - '.config('SITE_URL').'-'.config('SITE_TITLE')]);
		return $this->fetch();
	}
	//兑换商品收藏
	public function ex_collect(){
		$list = Db::name('goods')->alias('a')->field('a.*,w.id')->join('collect w','a.goods_id=w.goods_id')->where(['w.uid'=>member('uid')])->select();
       $this->assign('list',$list);
		$this->assign('number',['1'=>$this->num(1),'3'=>$this->num(3),'4'=>$this->num(4)]);
		$this->assign('member',User::get_logined_user());
		$this->assign('SEO',['title'=>'兑换商品收藏 - '.config('SITE_URL').'-'.config('SITE_TITLE')]);
		return $this->fetch();
	}
	//收藏移除
	public function remove(){
		
		$a = Db::name('collect')->where(['id'=>input('post.id')])->delete();
		if ($a) {
			return true;
		}else{
			return false;
		}

	}
}
