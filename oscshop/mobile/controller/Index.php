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
use osc\member\service\User;
use osc\common\service\Goods as GoodsService;
class Index extends MobileBase
{
 	public function index()
    {
		$this->assign('cate', config('membership'));
		$this->assign('empty', '~~暂无数据');
		$this->assign('list', $this->sel(28));
		$this->assign('list1', $this->sel(29));
        $this->assign('banner', Db::name('ads_items')->where('ad_id',1)->select());
		$this->assign('SEO',['title'=>config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }



	 public function sel($id){
       $list= Db::name('goods')->alias('a')->where('a.end_time','>',date('Y-m-d,H-i-s'))->join('goods_to_category w','a.goods_id = w.goods_id')->where(['w.category_id'=>$id,'a.status'=>1])->order('a.goods_id desc')->limit(2)->select();
       foreach ($list as $key => $v) {
           $list[$key]['end_time']=strtotime($v['end_time'])*1000;
       }
       return $list;
    }


}
