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
 *会员账户资料相关
 */
namespace osc\app\controller;
use think\Db;
use osc\common\model\Member;
class Account extends APP{
	
	protected function _initialize(){
		parent::_initialize();


	}
	//我的资料
    public function profile(){

		if(request()->isPost()){

			$data=$this->getData();

			if (isset($data['userpic'])) {

				$name=substr(md5(rand(454662,785779)),5,12).'.jpg';
				$file=base64_decode($data['userpic']);
				file_put_contents('./public/uploads/images/product/'.$name, $file);

				$member['userpic']=$name;
			}

			$member['nickname']=$data['nickname'];
			$men=Member::get($data['uid']);
			if($men->save($member)){
				$memb=Db::name('member')->where('uid',$data['uid'])->find();
				$me=$this->hand_img($memb,'userpic');

				return ['code'=>400,'msg'=>'修改成功','data'=>$me];


			}else{
				return ['code'=>404,'msg'=>'修改失败'];
			}
		}
		

    }
	//修改密码
	public function password(){
		if(request()->isPost()){

			$data = $this->getData();

			if ($data['code'] != $data['vcode']) {


				$this->show(405,'验证码不正确');die;
			}
			$member=Member::get($data['uid']);
			if ($data['telephone'] != $member['telephone']) {
				$this->show(405,'您输入的手机号和绑定收机不一样');die;

			}



			$member->password = think_ucenter_encrypt($data['password'], config('PWD_KEY'));

			if ($member->save()) {

				$this->show(400,'修改成功');die;
			} else {
				$this->show(405,'修改失败');die;
			}
		}

	}

	public function address(){

		$address=Db::name('address')->where('uid',input('uid'))->select();
		return ['code'=>'400','msg'=>'请求数据成功','data'=>$address];
	}
	public function ads_ajax(){
		$address = Db::name('address')->where('uid',input('uid'))->select();
		$add     = Db::name('address')->where('uid',0)->select();
		return ['code'=>400,'msg'=>'获取数据成功','data'=>['address'=>$address,'add'=>$add]];
	}
	function add_address(){
		


			$data=$this->getData();
			$validate=new \osc\member\validate\Shipping();

			$data['area']=explode(' ',$data['area']);
			$dat['province_id']=$data['area'][0];
			$dat['city_id']=$data['area'][1];
			$dat['country_id']=$data['area'][2];
			$dat['name']=$data['name'];
			$dat['telephone']=$data['telephone'];
			$dat['address']=$data['address'];

			if (!$validate->check($data)) {			    
				return ['code'=>404,'msg'=>$validate->getError()];
			}

			$dat['uid']=$data['uid'];

			if(Db::name('address')->insert($dat)){
				return ['code'=>400,'msg'=>'新增成功'];
			}else{
				return ['code'=>404,'msg'=>'新增失败'];
			}

	}

	function edit_address(){
		
		if(request()->isPost()){

			$data=input('post.');
			$data['area']=explode(' ',$data['area']);
			$address['province_id']=$data['area'][0];
			$address['city_id']=$data['area'][1];
			$address['country_id']=$data['area'][2];
			$address['name']=$data['name'];
			$address['address']=$data['address'];
			$address['telephone']=$data['telephone'];
			$validate=new \osc\member\validate\Shipping();

			if (!$validate->check($address)) {
				return ['code'=>404,'msg'=>$validate->getError()];
			}
//			$address['uid']=UID;

			if(Db::name('address')->where('address_id',$data['address_id'])->update($address)){
				return ['code'=>400,'msg'=>'修改成功'];
			}else{
				return ['code'=>404,'msg'=>'修改失败'];
			}	
		}
	}
	function del_address(){
		$map['uid']=['eq',UID];
		$map['address_id']=['eq',(int)input('param.id')];
		
		if(Db::name('address')->where($map)->delete()){
			$this->success('删除成功！！',url('Account/address'));
		}else{
			$this->error('删除失败！！');
		}
		
		
	}
	public function phone(){
		if (request()->isPost()) {

			$data=input('post.');

			if (think_ucenter_encrypt($data['password'],config('PWD_KEY'))!=member('password')) {

				$this->show(405,'密码不正确');die;
			}

			if( Db::name('member')->where('username',$data['telephone'])->find() ){
				$this->show(405,'手机号已被绑定');die;
			}
			if($data['code']!=$data['vcode']){

				$this->show(405,'验证码不正确');die;
			}
			$mem=Member::get($data['uid']);
			if($mem->save(['username'=>$data['telephone']])){
				$this->show(400,'修改成功',$mem);die;
			}else{
				$this->show(405,'修改失败');die;
			}




		}
		
		return $this->fetch();
	}
	public function ajaxAddress(){

			$address = Db::name('address')->where('address_id',input('post.id'))->find();

		
		return ['code'=>400,'msg'=>'请求数据成功','$data'=>$address];
	}
	
}
