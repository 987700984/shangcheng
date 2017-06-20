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
class Membership extends MobileBase
{
    

    public function index()
    {   
        
        // dump($this->sel());die;
        $this->assign('list',$this->sel(input('param.id')));
        $this->assign('area',Db::name('goods_area')->select());
		    $this->assign('SEO',['title'=>config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		      
		 return $this->fetch();
   
    }

    public function sel($id){
       $list= Db::view('goods','*')->view('GoodsToCategory','category_id','GoodsToCategory.goods_id=goods.goods_id')->where('GoodsToCategory.category_id',$id)->select();
       // dump($list);die;
       foreach ($list as $key => $v) {
          if ($v['end_time']!==NULL) {
            
            if ( strtotime($v['end_time'])<time() ) {
              $list[$key]['end_time']=1;

            }else{
               $list[$key]['end_time']=strtotime($v['end_time'])*1000;
              
            }
          }
       }
       // var_dump($list[3]);die;
       return $list;
    }



}
