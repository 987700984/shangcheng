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
use think\Request;
use osc\common\model\Goods;
class Index extends HomeBase
{



    public function index()
    {


//        dump(config('SITE_URL'));die;
        if(request()->isGet()){
//            dump(input('param.states'));
            $this->assign('states',input('param.states'));
        }
		$this->assign('empty', '~~暂无数据');

        $this->assign('list1',$this->sel('0',4,'goods_id'));
        $this->assign('list4',$this->sell(4));
        $this->assign('list2',Db::name('goods')->where(['is_points_goods'=>1,'status'=>1])->order("goods_id desc")->limit(8)->select());
        $this->assign('list3',Db::name('goods')->where(['is_points_goods'=>1,'status'=>1])->order("viewed desc")->limit(4)->select());
        $this->assign('banner',Db::name('ads_items')->where('ad_id',4)->select());
		$this->assign('SEO',['title'=>config('SITE_TITLE'),'keywords'=>config('SITE_KEYWORDS'),'description'=>config('SITE_DESCRIPTION')]);
		
		return $this->fetch();
   
    }

    public function article(){
        if(request()->isGet()){
            $this->assign('list',Db::view('message','*')->view('admin','user_name','admin.admin_id=message.author')->where('message.id',input('param.id'))->find());
            $this->assign('SEO',['title'=>'文章-'.config('SITE_URL').config('SITE_TITLE')]);
            return $this->fetch();
        }else{
            $this->error('参数错误');
        }

    }


    public function sel($is_points_goods,$num,$con){
       $list= Db::name('goods')->where('end_time','>',date('Y-m-d,H-i-s'))->where(['is_points_goods'=>$is_points_goods,'status'=>1])->order("$con desc")->limit($num)->select();
       if ($is_points_goods==0) {
          foreach ($list as $key => $v) {
           $list[$key]['end_time']=strtotime($v['end_time'])*1000;
          }
       }
       
       return $list;
    }


}
