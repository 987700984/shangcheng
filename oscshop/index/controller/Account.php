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
namespace osc\index\controller;
use osc\common\controller\HomeBase;
use think\Db;
use osc\member\service\User;
class Account extends HomeBase{
	
	protected function _initialize(){
		parent::_initialize();
		define('UID',User::is_login());
		if(!UID){
			$this->error('请先登录','Index/index');
		}
	}
	//我的资料
    public function profile(){	

		if(request()->isPost()){
			$data=input('post.');


			if (!empty(request()->file('userpic'))) {
				$file=request()->file('userpic');
				$info = $file->rule('uniqid')->move( 'public/uploads/images/product');	
				$member['userpic']=$info->getSaveName();
			}


			if(Db::name('member')->where('uid',member('uid'))->update($member,false,true)){

				User::get_logined_user()->storage_user_action('修改了系统个人资料');
				
				$this->success('修改成功！',url('Account/profile'));
			}else{
				$this->error('修改失败！');
			}
		}
		
		$this->assign('user',User::get_logined_user());
		
		$this->assign('breadcrumb2','我的资料');
		$this->assign('SEO',['title'=>'我的资料-'.config('SITE_URL').'-'.config('SITE_TITLE')]);
		return $this->fetch();   
    }
	//修改密码
	public function password()
	{
		if (request()->isPost()) {

			$data = input('post.');

			if (!$data['code'] == cookie('code')) {


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

	}
	//修改名字
	public function update_name(){
		$update = Db::name('member')->where('uid',member('uid'))->update(input('post.'));
		if($update){
			return ['success'=>'修改成功'];
		}else{
			return ['error'=>'修改失败'];
		}
	}

	public function address(){
		
		$this->assign('list',Db::name('address')->where('uid',member('uid'))->paginate(config('page_num')));
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		$this->assign('SEO',['title'=>'地址簿-'.config('SITE_URL').'-'.config('SITE_TITLE')]);
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
		

			$data=input('post.');
			$validate=new \osc\member\validate\Shipping();		

			
			if (!$validate->check($data)) {			    
				return $validate->getError();
			}
			$data['uid']=UID;
			if(Db::name('address')->insert($data)){
				return ['success'=>'新增成功'];
			}

	}
	function edit_address(){



			if(request()->isPost()){

				$data=input('post.');
				$validate=new \osc\member\validate\Shipping();

				if (!$validate->check($data)) {

					return ['error'=> $validate->getError()];
				}

//			$address['uid']=UID;

				User::get_logined_user()->storage_user_action('修改了收货地址');

				if(Db::name('address')->where('address_id',$data['address_id'])->update($data)){
					return ['success'=>'修改成功'];
				}else{

					return ['error'=> '修改失败'];
				}
			}

	}
	function del_address(){


		
		if(Db::name('address')->where('address_id',input('param.id'))->delete()){
			User::get_logined_user()->storage_user_action('删除了收货地址');
			return ['success'=>'删除成功！'];
		}else{
			return ['error'=>'删除失败！'];
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
