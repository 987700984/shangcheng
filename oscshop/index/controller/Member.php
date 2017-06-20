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
use osc\common\model\Product;
use osc\common\model\Order;
use think\Db;
use osc\member\service\User as UserService ;
class Member extends 	HomeBase{
	
	protected function _initialize(){
		parent::_initialize();
		define('UID',UserService::is_login());

		$this->assign('breadcrumb2','订单管理');
		$this->assign('breadcrumb1','我的订单');
		
	}
	public function index(){
		if(!UID){
			$this->error('请先登录！');
		}


		$this->assign('list',osc_order()->order_list(input('param.'),20,member('uid')));
		$this->assign('collect_me',Db::view('goods','*')->view('collect','id','collect.goods_id=goods.goods_id')->where('goods.is_points_goods',0)->order('collect.id desc')->limit(6)->select());
		$this->assign('collect',Db::view('goods','*')->view('collect','id','collect.goods_id=goods.goods_id')->where('goods.is_points_goods',1)->order('collect.id desc')->limit(6)->select());
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		$this->assign('SEO',['title'=>'会员中心-'.config('SITE_TITLE')]);
		return $this->fetch();
	}


	public function product(){
		if (request()->isPost()) {
		if(!UID){
			$this->error('请先登录');
		}
		$data=input('post.');
		$data['status']=2;
		$data['uid']=member('uid');	
		$data['add_time']=date('Y-m-d,H:i:s');

			$pro= new Product($data);

			$id=$pro->allowField(true)->save();
			if(!empty($_FILES) &&$id==1){
				$this->image();
			}
			$this->success('提交成功！');
		}
		$this->assign('SEO',['title'=>'产品入会-'.config('SITE_URL').'-'.config('SITE_TITLE')]);
		return $this->fetch();
		
		
	}
//删除记录
	public function remove(){
		if(!UID){
			return ['error'=>'请先登录！'];
		}
		if(!Product::get(input('param.id'))){
			return ['error'=>'非法操作！'];
		}
		$de  = Product::destroy(input('param.id'));
		$del = Db::name('product_image')->where('pid',input('param.id'))->delete();
		if ($del && $de) {
			return ['success'=>'已取消！'];
		}
	}
	public function ticketTime(){
		if(!UID){
			$this->error('请先登录！');
		}
		$order = Db::name('goods_ticket')->where('uid',member('uid'))->where('start_time','>',strtotime("-1years",time()))->select();
		foreach ($order as $key => $val) {

			$order[$key]['start_time']=strtotime("1months",$val['start_time']);
			$order[$key]['end_time']=strtotime("1years",$val['start_time']);
			if ($val['category_id']==28) {
				$val['cash_point']=$val['cash_points']+1;

			}

			$order[$key]['total_points']=$val['cash_point']+$val['none_points'];
		}
		// dump($order);die;
		$this->assign('SEO',['title'=>'兑换账期 - '.config('SITE_URL').'-'.config('SITE_TITLE')]);
		$this->assign('order',$order);
		$this->assign('points',member('points'));
		$this->assign('point',member('cash_points'));
		return $this->fetch();
	}

//产品兑换记录
	public function pr_record(){
		if(!UID){
			$this->error('请先登录');
		}
		$list=Db::name('product')->where('uid',member('uid'))->order('id desc')->paginate(config('page_num'));

		$lis=array();
		foreach ($list as $k => $v) {
			$lis[$k]=$v;
			$img = Db::name('product_image')->field('image')->where('pid',$v['id'])->find();
			
			foreach ($img as $key => $value) {
				$lis[$k]['img']=$value;
			}
			
		}
		// dump($lis);
		$this->assign('list',$lis);
		$this->assign('SEO',['title'=>'兑换记录-'.config('SITE_URL').'-'.config('SITE_TITLE')]);
		$this->assign('page',$list->render());
		return $this->fetch();
	}
	public function recharge(){
		if (request()->isPost()) {
			if(!UID){

				$this->error('请先登录');

			}
		}
		$this->assign('charge',config('charge_config'));
		$this->assign('SEO',['title'=>'购买兑换券-'.config('SITE_URL').'-'.config('SITE_TITLE')]);
		return $this->fetch();
	}












	public function image(){
	
		$files=request()->file('file');
			foreach($files as $file){
	   		 	$info = $file->rule('uniqid')->move( 'public/uploads/images/product');
	        	if($info){
	        		$product = Product::order('id desc')->find();
	            	$product->images()->save(['image'=>$info->getSaveName()]);
	        	}else{
	            	echo $file->getError();
	        	}
			}

			
	}
	
}
