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
use osc\common\model\Goods as GoodsModel;
use think\Db;
class Goods extends HomeBase
{
    public function index()
    {    
    	
		 if(!$list=osc_goods()->get_goods_info((int)input('param.id'))){
		 	$this->error('商品不存在！！');
		 }
		  if ($list['goods']['end_time']!==NULL) {
             if ( strtotime($list['goods']['end_time'])<time() ) {
               $list['goods']['end_time']=1;
             }else{
                $list['goods']['end_time']=strtotime($list['goods']['end_time'])*1000;
             }
           }
		$comment=Db::name('goods_comment')->where(['goods_id'=>input('param.id'),'status'=>1])->order('id desc')->limit(2)->select();
		foreach($comment as $key=>$v){
			$comment[$key]['phone']=substr_replace($v['phone'],'*********',1,9);
		}
		$this->assign('count',Db::name('goods_comment')->where(['goods_id'=>input('param.id'),'status'=>1])->count());
		 $this->assign('comment',$comment);
		 $this->assign('SEO',['title'=>$list['goods']['name'].'-'.config('SITE_URL').'-'.config('SITE_TITLE'),
		 'keywords'=>$list['goods']['meta_keyword'],
		 'description'=>$list['goods']['meta_description']]);
		 $good = GoodsModel::get((int)input('param.id'));
		 $good->updateViewed();
		 $this->assign('list',Db::name('goods_attribute')->alias('a')->where('a.goods_id',input('param.id'))->join('attribute_value w','a.attribute_value_id = w.attribute_value_id')->select());
         $this->assign('collect',Db::name('collect')->where(['uid'=>member('uid'),'goods_id'=>$list['goods']['goods_id'],'is_points_goods'=>0])->find());
		 $this->assign('goods',$list['goods']);
		 $this->assign('image',$list['image']);
		$this->assign('list4',$this->sell(4));
		 $this->assign('empty','&nbsp;&nbsp;&nbsp;&nbsp;暂时还没有人评论!');
		return $this->fetch();
    }

    public function ajaxIndex(){
    	if (request()->isAjax()) {
    		$list=Db::name('goods_comment')->where(['goods_id'=>input('post.goods_id')-1,'status'=>1])->order('id desc')->select();
    		if ($list) {
    			return $list;
    		}else{
    			return false;
    		}
    	}	
    }


}
