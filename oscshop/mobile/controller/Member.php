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
use osc\common\model\Product;
use osc\common\model\Order;
use think\Db;
use osc\member\service\User as UserService ;
class Member extends MobileBase{
	
	protected function _initialize(){
		parent::_initialize();
		define('UID',UserService::is_login());
		
		$this->assign('breadcrumb2','订单管理');
		$this->assign('breadcrumb1','我的订单');
		
	}
	public function index(){
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
		$this->assign('member',UserService::get_logined_user());
		$order= new Order();
		  
		 $this->assign('number',['1'=>$this->num($order,1),'3'=>$this->num($order,3),'4'=>$this->num($order,4)]);
		$this->assign('list',osc_order()->order_list(input('param.'),20,member('uid')));
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		$this->assign('SEO',['title'=>'会员中心-'.config('SITE_TITLE')]);
		return $this->fetch();
	}
//统计订单数量
	public function num($order,$id){
		$count = $order->where(['order_status_id'=>$id,'uid'=>member('uid')])->count();
		return $count;
	}

	public function product(){

		if (request()->isPost()) {
			if(!UID){
				if(!in_wechat()){
					$this->error('请先登录');
				}else{
					$this->error('系统错误');
				}
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
		$this->assign('SEO',['title'=>'产品入会-'.config('SITE_TITLE')]);
		return $this->fetch();
		
		
	}
//删除记录
	public function remove(){
		$de  = Product::destroy(input('param.id'));
		$del = Db::name('product_image')->where('pid',$input('param.id'))->delete();
		if ($del && $de) {
			return true;
		}
	}
//推荐人
	public function referrer(){
		$referrer=member('pid');
		if($referrer==null){
			return ['error'=>'您没有推荐人'];
		}
		$add=Db::name('member')->where('uid',$referrer)->find();
		return ['success'=>"您的推荐人是：".$add['nickname']];
	}

//产品兑换记录
	public function pr_record(){
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
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
		$this->assign('SEO',['title'=>'兑换记录-'.config('SITE_TITLE')]);
		$this->assign('page',$list->render());
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
