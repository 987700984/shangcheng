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
use think\Request;
use osc\common\model\Goods;
class Exshop extends MobileBase
{
    public function index()
    {
		$this->assign('empty', '~~暂无数据');
        $this->assign('banner', Db::name('ads_items')->where('ad_id',2)->select());
		$this->assign('list',array_chunk( Db::name('goods')->where(['is_points_goods'=>1,'status'=>1])->order('goods_id desc')->limit(10)->select(),2));
		$this->assign('cate',Db::name('category')->select());
		$this->assign('SEO',['title'=>config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }
}
