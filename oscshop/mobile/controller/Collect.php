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

 
namespace osc\mobile\controller;
use think\Db;
class Collect extends MobileBase{


	//	点击收藏和取消收藏
	public function save(){
		if(!$uid=member('uid')){
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
				Db::name('collect')->where(['uid'=>$data['uid'],'goods_id'=>$data['goods_id'],])->delete();
				break;
			case 'add1':
				$data['is_points_goods']=1;
				Db::name('collect')->insert($data);
				break;
			default:
				$data['is_points_goods']=1;
				Db::name('collect')->where(['uid'=>$data['uid'],'goods_id'=>$data['goods_id']])->delete();
				break;
		}

		return ['success'=>'成功'];
	}
	//入会关注
	public function member_col(){
		if(!$uid=member('uid')){
			$this->redirect('Login/user_login');
		}
		$list = Db::name('goods')->alias('a')->field('a.*,w.id')->join('collect w','a.goods_id=w.goods_id')->where(['a.is_points_goods'=>0,'w.uid'=>member('uid')])->select();
		 foreach ($list as $key => $v) {
          if ($v['end_time']!==NULL) {
            
            if ( strtotime($v['end_time'])<time() ) {
              $list[$key]['end_time']=1;

            }
          }
       }
       $this->assign('SEO',['title'=>'入会商品收藏-'.config('SITE_TITLE')]);
       $this->assign('list',$list);
		return $this->fetch();
	}
	//兑换商品收藏
	public function ex_collect(){
		if(!$uid=member('uid')){
			$this->redirect('Login/user_login');
		}
		$list = Db::name('goods')->alias('a')->field('a.*,w.id')->join('collect w','a.goods_id=w.goods_id')->where(['a.is_points_goods'=>1,'w.uid'=>member('uid')])->select();

		$this->assign('SEO',['title'=>'兑换商品收藏-'.config('SITE_TITLE')]); 
       $this->assign('list',$list);
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
