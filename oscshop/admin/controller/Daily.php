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
use think\Db;
use think\Controller;

class Daily extends controller
{

//定时返还兑换券
	public function return_ticket(){
		
		$list = Db::view(['Goods_ticket','o'],'*')->view('Goods','points','Goods.goods_id=o.pid')->select();
		foreach ($list as $key => $value) {
			if ($value['points']>40) {
				$return_time=strtotime("1weeks",$value['return_time']);
			}else{
				$return_time=strtotime("1months",$value['return_time']);	
			}
			if ($return_time<=time() && $value['none_points']!==0) {
				Db::name('goods_ticket')->where('id',$value['id'])->setInc('cash_points',1);
				Db::name('goods_ticket')->where('id',$value['id'])->setDec('none_points',1);
				Db::name('member')->where('uid',$value['uid'])->setInc('cash_points',1);
				Db::name('member')->where('uid',$value['uid'])->setDec('points',1);
				Db::name('goods_ticket')->where('id',$value['id'])->update(['return_time'=>$return_time]);
			}	
		}
	}


















}