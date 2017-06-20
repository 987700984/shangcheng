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

 
namespace osc\app\controller;
use think\Db;
class Collect extends APP{


	//	点击收藏和取消收藏
	public function save()
	{
			$dat=$this->getData();
			$data['uid'] = $dat['uid'];
			$data['goods_id'] = $dat['goods_id'];

			switch ($dat['id']) {
				case 'add0':
					$data['is_points_goods'] = 0;
					$da=Db::name('collect')->insert($data);
					break;
				case 'add1':
					$data['is_points_goods'] = 1;
					$da=Db::name('collect')->insert($data);
					break;
				default:
					$da=Db::name('collect')->where(['uid' => $data['uid'], 'goods_id' => $data['goods_id']])->delete();
					break;
			}
			if($da){
				return ['code' => 400, 'msg' => '操作成功'];
			}



	}
	//入会关注
	public function member_col(){
		if(request()->isPost()){

			$list = Db::name('goods')->alias('a')->field('a.*,w.id')->join('collect w','a.goods_id=w.goods_id')->where(['a.is_points_goods'=>0,'w.uid'=>input('post.uid')])->select();
			 foreach ($list as $key => $v) {
				  if ($v['end_time']!==NULL) {

					  if (strtotime($v['end_time']) < time()) {
						  $list[$key]['end_time'] = 1;

					  }
				  }
			 }
			$lis=$this->handle_img($list,'image',100,100);
			return ['code' => 400, 'msg' => '请求数据成功', 'data' => $lis];
       	}

	}
	//兑换商品收藏
	public function ex_collect(){

		$lis = Db::name('goods')->alias('a')->field('a.*,w.id')->join('collect w','a.goods_id=w.goods_id')->where(['a.is_points_goods'=>1,'w.uid'=>input('post.uid')])->select();
		$list=$this->handle_img($lis,'image',100,100);
		return ['code' => 400, 'msg' => '请求数据成功', 'data' => $list];

	}
	//收藏移除
	public function remove(){

		$a = Db::name('collect')->where(['id'=>input('post.id')])->delete();
		if ($a) {
			return ['code' => 400, 'msg' => '删除成功', 'data' => ''];
		}else{
			return ['code' => 405, 'msg' => '删除失败', 'data' => ''];
		}
		
	}
}
