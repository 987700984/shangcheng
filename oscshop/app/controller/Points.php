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
 * 积分,积分兑换
 */
 
namespace osc\mobile\controller;
use think\Db;
use osc\member\service\User;
class Points extends MobileBase
{
	protected function _initialize(){
		parent::_initialize();						
		define('UID',User::is_login());
		//手机版
		if(!UID){
			if(!in_wechat()){
				$this->redirect('login/user_login');	
			}else{
				$this->error('系统错误');
			}			
		}		
	}
	
	function index(){

		$this->assign('SEO',['title'=>'积分兑换']);
		
		$this->assign('top_title','积分兑换');
		$this->assign('flag','user');
		return $this->fetch();
	}
	
	public function ajax_goods_list(){

        $page=input('param.page');//页码

        $limit = (6 * $page) . ",6";
		
        $list=Db::name('goods')->where('is_points_goods',1)->order('goods_id desc')->limit($limit)->select();
		
		if(isset($list)&&is_array($list)){
				foreach ($list as $k => $v) {				
					$list[$k]['image']=resize($v['image'], 250, 250);		
				}
		}
		
        return  $list;
    }
	//商品详情
	function detail(){
		
		if(!$list=osc_goods()->get_goods_info((int)input('param.id'))){
			$this->error('商品不存在！！');
		}
		
		$this->assign('SEO',['title'=>$list['goods']['name'].'-'.config('SITE_TITLE'),
		'keywords'=>$list['goods']['meta_keyword'],
		'description'=>$list['goods']['meta_description']]);	
		
		$this->assign('top_title',$list['goods']['name']);
		$this->assign('goods',$list['goods']);
		$this->assign('image',$list['image']);
		$this->assign('options',$list['options']);
		$this->assign('discount',$list['discount']);
		$this->assign('mobile_description',$list['mobile_description']);		
		
		if(in_wechat())
		$this->assign('signPackage',wechat()->getJsSign(request()->url(true)));	
		
		$this->assign('points_goods','points');			
        return $this->fetch('goods:detail');
	}
	function points_list(){
		$this->assign('user_info',Db::name('member')->where(array('uid'=>UID))->find());	
		$this->assign('list',Db::name('points')->where(array('uid'=>UID))->select());		
		$this->assign('empty',"<span style='margin-left:20px;'>没有数据</span>");
		$this->assign('top_title','我的积分');
		return $this->fetch();
	}
}
