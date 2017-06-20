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
use osc\common\model\Product;
use osc\common\model\Member as MemberModel;
use osc\common\model\Order;
use think\Db;
class Member extends APP{
	
	protected function _initialize(){
		parent::_initialize();

		
	}
	public function index(){
		$post=$this->getData();
		$order= new Order();
		$ord=['one'=>$this->num($order,1,$post['uid']),'three'=>$this->num($order,3,$post['uid']),'four'=>$this->num($order,4,$post['uid'])];

		$this->show('400','成功',$ord);
	}
//统计订单数量
	public function num($order,$id,$uid){
		$count = $order->where(['order_status_id'=>$id,'uid'=>$uid])->count();
		return $count;
	}

	public function product(){

		$this->getData();
		$data=input('post.');
		$dat['add_time']=date('Y-m-d H:i:s',time());
		$dat['name']=$data['username'];
		$dat['number']=$data['number'];
		$dat['phone']=$data['phone'];
		$dat['remark']=$data['remark'];
		$dat['uid']=$data['uid'];
		$dat['status']=2;
		$pro=new Product($dat);
		$pro->save();
		$num=str_replace('s','', $data['num']);
        //开始移动文件到相应的文件夹
		if($_FILES){

			$dir=ROOT_PATH.'public/uploads/images/product/';
			$this->makedir($dir);
			for($i=0;$i<$num;$i++){
				$im='img'.$i;
				$filename=md5(rand(0,9999)).'.'.substr($_FILES[$im]['type'],6);
				move_uploaded_file($_FILES[$im]['tmp_name'],$dir .$filename);

				$pro->images()->save(['image'=>$filename,'pid'=>$pro->id]);
			}
		}


		
	}


//删除记录
	public function remove(){
		if(!input('param.uid')){
			return ['code'=>404,'msg'=>'请先登录'];
		}
		$de  = Product::destroy(input('param.id'));
		$del = Db::name('product_image')->where('pid',input('param.id'))->delete();
		if ($del && $de) {
			return ['code'=>400,'msg'=>'删除成功'];
		}
	}
//推荐人
	public function referrer(){
		$uid=input('post.uid');
		$member=MemberModel::get($uid);
		if($member['pid']==null){
			return ['code'=>404,'msg'=>'您没有推荐人'];
		}
		$add=Db::name('member')->where('uid',$member['pid'])->find();
		return ['code'=>400,'msg'=>"您的推荐人是：".$add['nickname']];
	}

//产品兑换记录
	public function pr_record(){

		$list=Db::name('product')->where('uid',input('uid'))->order('id desc')->select();


		foreach ($list as $k => $v) {
			$img = Db::name('product_image')->field('image')->where('pid',$v['id'])->find();
			
			foreach ($img as $key => $value) {
				$list[$k]['img']=$value;
			}
			
		}
		$lis=$this->hand_img($list,'img');
		return ['code'=>400,'msg'=>'获取数据成功','data'=>$lis];


	}



	public function makedir($dir){

		if(!is_dir($dir))
		{
			mkdir($dir);
		}


	}












	public function image(){

		$files=$_FILES;
		foreach($files as $file){
			$info = $file->rule('uniqid')->move( 'public/uploads/images/product');
			if($info){
				$product = Product::order('id desc')->find();
				$product->images()->save(['image'=>$info->getSaveName()]);
			}else{
				echo ['code'=>404,'msg'=>$file->getError()];
			}
		}


	}
	
}
