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
namespace osc\mobile\controller;
use think\Db;
use osc\member\service\User;
class Account extends MobileBase{
	
	protected function _initialize(){
		parent::_initialize();
		define('UID',User::is_login());
		if(!UID){
			if(in_wechat()){
				$this->redirect('login/user_login');
			}else{
				$this->error('系统错误');
			}
		}
	}
	//我的资料
    public function profile(){

		if(request()->isPost()){

			$data=input('post.');

			if (isset($data['userpic'])) {

				$name=substr(md5(rand(454662,785779)),5,12).'.jpg';
				$file=base64_decode($data['userpic']);
				file_put_contents('./public/uploads/images/product/'.$name, $file);

				$member['userpic']=$name;
			}

			$member['nickname']=$data['nickname'];

			if(Db::name('member')->where('uid',member('uid'))->update($member,false,true)){

				return ['code'=>400,'msg'=>'修改成功'];


			}else{
				return ['code'=>404,'msg'=>'修改失败'];
			}
		}

		$this->assign('user',User::get_logined_user());
		
		$this->assign('breadcrumb2','我的资料');
		$this->assign('SEO',['title'=>'我的资料-'.config('SITE_TITLE')]);
		return $this->fetch();   
    }
	//修改密码
	public function password(){
		if(request()->isPost()){

			$data = input('post.');

			if ($data['code'] != cookie('code')) {


				return ['error' => '验证码错误'];
			}

			if ($data['telephone'] != member('telephone')) {
				return ['error' => '您输入的手机号和绑定收机不一样'];
			}
			$user_info = User::get_logined_user();


			$user_info->password = think_ucenter_encrypt($data['password'], config('PWD_KEY'));

			if ($user_info->save()) {

				User::get_logined_user()->storage_user_action('修改了登录密码');
				return ['success' => '修改成功'];
			} else {
				return ['error' => '修改失败'];
			}
		}
		$this->assign('breadcrumb2','修改密码');
		$this->assign('SEO',['title'=>'修改密码-'.config('SITE_TITLE')]);
		return $this->fetch(); 
	}

	public function address(){

		$this->assign('list',Db::name('address')->where('uid',member('uid'))->paginate(config('page_num')));
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		$this->assign('SEO',['title'=>'地址簿-'.config('SITE_TITLE')]);
		$this->assign('breadcrumb2','个人资料');
		$this->assign('crumbs','地址簿');
		return $this->fetch(); 
	}
	public function ads_ajax(){
		$address = Db::name('address')->where('uid',member('uid'))->select();
		$add     = Db::name('address')->where('uid',0)->select();
		return ['list'=>$address,'lift'=>$add];
	}
	function add_address(){
		
		if(request()->isPost()){

			$data=input('post.');
			$validate=new \osc\member\validate\Shipping();

			$data['area']=explode(' ',$data['area']);
			$dat['province_id']=$data['area'][0];
			$dat['city_id']=$data['area'][1];
			$dat['country_id']=$data['area'][2];
			$dat['name']=$data['name'];
			$dat['telephone']=$data['telephone'];
			$dat['address']=$data['address'];

			if (!$validate->check($data)) {			    
				return ['error'=>$validate->getError()];
			}

			$dat['uid']=UID;

			if(Db::name('address')->insert($dat)){
				return ['success'=>'新增成功'];
			}
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
				return ['error'=> $validate->getError()];
			}
//			$address['uid']=UID;

			User::get_logined_user()->storage_user_action('修改了收货地址');
			if(Db::name('address')->where('address_id',$data['address_id'])->update($address)){
				return ['success'=>'修改成功'];
			}else{
				return ['error'=> '修改失败'];
			}	
		}
	}
	function del_address(){
		$map['uid']=['eq',UID];
		$map['address_id']=['eq',(int)input('param.id')];
		
		if(Db::name('address')->where($map)->delete()){
			User::get_logined_user()->storage_user_action('删除了收货地址');
			$this->success('删除成功！！',url('Account/address'));
		}else{
			$this->error('删除失败！！');
		}
		
		
	}
	public function phone(){
		if (request()->isPost()) {

			$data=input('post.');

			if (think_ucenter_encrypt($data['password'],config('PWD_KEY'))!=member('password')) {

				return ['error'=>'密码不正确'];
			}

			if( Db::name('member')->where('telephone',$data['telephone'])->find() ){
				return ['error'=>'手机号已被绑定'];
			}
			if($data['code']!=cookie('code')){

				return ['error'=>'验证码不正确'];
			}
			$user_info=User::get_logined_user();
			$user_info -> telephone =$data['telephone'];

			if($user_info->save()){

				User::get_logined_user()->storage_user_action('修改了手机号');
				return ['success'=>'修改成功'];
			}
		}
		
		return $this->fetch();
	}
	public function ajaxAddress(){
		if (input('param.id')==0) {
			$address = Db::name('address')->where(['uid'=>input('param.id')])->find();
		}else{
			$address = Db::name('address')->where('address_id',input('param.id'))->find();
		}
		
		return ['address'=>$address];
	}
	
}
