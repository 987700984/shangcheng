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

class Exshop extends APP
{
    public function index()
    {
		if(request()->isPost()){

			$banner=$this->handle_img(Db::name('ads_items')->where('ad_id',2)->select(),'image_url',300,300);
			$list=$this->handle_img(Db::name('goods')->where(['is_points_goods'=>1,'status'=>1])->order('goods_id desc')->limit(10)->select(),'image',100,100);

//			dump($list);die;
			$data=['list'=>$list,'banner'=>$banner];
			return ['code'=>400 ,'msg'=>'请求数据成功','data'=>$data];
		}


		

   
    }
}
