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
use \think\Db;
use think\Request;
use osc\common\model\GoodsComment;
use osc\common\controller\Base;
class Comment extends HomeBase{


//	评论数据输出
	function index(){


	}





//	添加评论
	function commentAdd(){
		$data = Request::instance()->param();
		$result = $this->validate($data,'Comment');
		if(true !== $result) {
			$this->error($result);
		}else{

//微信端应该有网页验证，用户信息存在session中
			$goodsComment = new GoodsComment([
				'goods_id'=>$data['goods_id'],
				'content'=>$data['content'],
				'ip_address'=> Request::instance()->ip(),

//				这三项数据在session中取
				'phone' => '11',
				'user_name' => '11',
				'user_id' => '11',
			]);
			$result = $goodsComment->save();
			if($result){
				$this->success('评论发表成功');
			}else{
				$this->error('评论提交失败！');
			}
		}
	}




}
