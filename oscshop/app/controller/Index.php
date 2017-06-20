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
class Index extends APP
{
 	public function index()
    {

        if(request()->isPost()){
            $list=$this->handle_img($this->sel(28),'image',100,100);
            $banner=$this->handle_img(Db::name('ads_items')->where('ad_id',1)->select(),'image_url',300,300);

            $data=['list'=>$list,'banner'=>$banner];
            $this->show(400,'请求数据成功',$data);

        }
    }





	 public function sel($id){
       $list= Db::name('goods')->alias('a')->where('a.end_time','>',date('Y-m-d,H-i-s'))->join('goods_to_category w','a.goods_id = w.goods_id')->where(['w.category_id'=>$id,'a.status'=>1])->order('a.goods_id desc')->limit(2)->select();
       foreach ($list as $key => $v) {
           $list[$key]['end_time']=strtotime($v['end_time'])*1000;
       }
       return $list;
    }


}
