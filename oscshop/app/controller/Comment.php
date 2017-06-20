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
use \think\Db;
use think\Request;
use osc\common\model\GoodsComment;
use osc\common\controller\Base;
use osc\member\service\User as UserService;

class Comment extends MobileBase{


//	评论数据输出
	function index(){

	}


//	添加评论
	function commentAdd(){



		$user = UserService::get_logined_user();

		$data = Request::instance()->param();
		if(!$data['content']){
			$this->error('请填写评论！');
		}
//微信端应该有网页验证，用户信息存在session中
		$goodsComment = new GoodsComment([
				'goods_id'=>$data['goods_id'],
				'content'=>$data['content'],
				'ip_address'=> Request::instance()->ip(),
				'status'=>1,
//				这三项数据在session中取
				'phone' => $user->telephone?$user->telephone:110,
				'user_name' => $user->nickname,
				'user_id' => $user->uid,
		]);
		$result = $goodsComment->save();
		if($result){

			$this->success('评论发表成功',url(input('url'),['id'=>input('goods_id')]));
		}else{
			$this->error('评论提交失败！');
		}
	}
}
