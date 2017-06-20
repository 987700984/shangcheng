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
class Category extends HomeBase
{
    public function index()
    {    
		$param=input('param.');

		if(!$category=Db::name('category')->find((int)$param['id'])){
			$this->error('商品分类不存在！！');
		}
		$this->assign('SEO',['title'=>$category['name'].'-'.config('SITE_TITLE'),
		'keywords'=>$category['meta_keyword'],
		'description'=>$category['meta_description']]);
		$this->assign('category',Db::name('category')->where('pid',37)->select());	
		$this->assign('empty', '~~暂无数据');
		return $this->fetch();
   
    }
}
