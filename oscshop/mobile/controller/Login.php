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
use osc\common\model\Member;
use wechat\Wechat;
use osc\member\service\User;
class Login extends MobileBase{
	
	function logout(){
		User::logout();
		$this->redirect('/mobile');
	}

	
	//登录验证
	public function validate_login($data){
		
			if(empty($data['telephone'])){
				return ['error'=>'手机号'];
			}elseif(empty($data['password'])){
				return ['error'=>'密码必填'];
			}
			if(1==config('use_captcha')){				
				if(!check_verify($data['captcha'])){
					return ['error'=>'验证码错误'];
				}
			}
			$user=Db::name('member')->where('username',$data['telephone'])->find();
			if(!$user){
				return ['error'=>'账号不存在'];
			}elseif(($user['checked']==0)&&(1==config('reg_check'))){//需要审核
				return ['error'=>'该账号未审核通过'];
			}
			
			if(think_ucenter_encrypt($data['password'],config('PWD_KEY'))==$user['password']){
		
				$auth = array(
		            'uid'             => $user['uid'],
		            'username'        => $user['username'],
		            'nickname'        => $user['nickname'],
		            'group_id'		  => $user['groupid'],		                     
				 );

				User::store_logined_user($auth);
				
				$login['lastdate']=time();
				$login['loginnum']		=	array('exp','loginnum+1');
				$login['lastip']	=	get_client_ip();
				
				DB::name('member')->where('uid',$user['uid'])->update($login);
				$logined_user = User::get_logined_user();
				$logined_user->storage_user_action('登录了网站');
				
				return ['success'=>'登录成功','total'=>$logined_user->carts()->count_cart_total()];
			}else{
				return ['error'=>'密码错误'];
			}
	}
	public function user_login(){
		$this->assign('SEO',['title'=>'登录方式']);
		return $this->fetch();
	}
	
 	function login(){
	
		if(request()->isPost()){
			$data=input('post.');

			$r=$this->validate_login($data);

			if(isset($r['error'])){
				return $r;
			}elseif($r['success']){
				return ['success'=>'登录成功！','url'=>url('Member/index')];

			}
		}
		$this->assign('SEO',['title'=>'登录-'.config('SITE_TITLE')]);
		$this->assign('top_title','登录');
        return $this->fetch();
    }
// 忘记密码
	public  function  forget_pw(){
		if(request()->isPost()){

			$data = input('post.');

			if ($data['code'] != cookie('code')) {


				return ['error' => '验证码错误'];
			}

			$member= new Member();

			$user_info=$member->where('username',$data['telephone'])->find();


			 $da= think_ucenter_encrypt($data['password'], config('PWD_KEY'));

			if ($user_info->save($da)) {
				$logined_user = User::get_logined_user();
				$logined_user->storage_user_action('修改了登录密码');
				return ['success' => '修改成功','url'=>url('Member/index')];
			} else {
				return ['error' => '修改失败'];
			}
		}
		$this->assign('breadcrumb2','忘记密码');
		$this->assign('SEO',['title'=>'忘记密码-'.config('SITE_TITLE')]);
		return $this->fetch();
	}

	function reg(){
	
		if(request()->isPost()){

			$data=input('post.');

			if(!$data['code']==cookie('code')){

					return ['error'=>'验证码错误'];
			}
			if($data['username']!=cookie('phone')){
				return ['error'=>'该手机号不是发送验证码的手机号'];

			}
			$result = $this->validate($data,'Member');

			if(true !== $result){

				return ['error'=>$result];
			}

			$member['username']=$data['username'];
			$member['nickname']=$data['username'];
			$member['reg_type']='mobile';
			$member['checked']=1;
			$member['password']=think_ucenter_encrypt($data['password'],config('PWD_KEY'));
			$member['groupid']=config('default_group_id');
			
			$member['regdate']=time();
			$member['lastdate']=time();			
			
			$member['nickname']=$data['username'];
			if (isset($data['pid'])) {
				$pid = Db::name('member')->where('telephone',$data['pid'])->find();
				if ($pid) {
					$member['pid']=$pid['uid'];
				}
			}

			
			$uid=Db::name('member')->insert($member,false,true);
			
			if($uid){

				

					$auth = array(
		            'uid'             => $uid,
		            'username'        => $member['username'],		           
		            'group_id'		  => $member['groupid']		          	            
					 );

					User::store_logined_user($auth);

					User::get_logined_user()->storage_user_action('注册成为会员');

					return ['success'=>'注册成功','url'=>url('Member/index')];

				
			}else{
				return ['error'=>'注册失败'];
			}
			
		}
		$this->assign('SEO',['title'=>'注册-'.config('SITE_TITLE')]);
		$this->assign('top_title','注册');
        return $this->fetch();
    }
    public function wei_login(){
    	$wechat =wechat();
    	$openid = $wechat->getOpenId();
    	if ($openid) {
    		$status=$wechat->wechatAutoReg($openid);
    		if ($status) {
    			$this->success('登录成功！','Member/index');
    		}else{
    			$this->redirect('Login/blind',['states'=>1]);
    		}
    	}
    }
    public function blind(){
    	if(request()->isPost()){
			$data=input('post.');
			$result = $this->validate($data,'Member');

			if(true !== $result){
				$this->error($result);
			}
			if($data['code']!=cookie('code')){
				$this->error('验证码错误');
			}
			if($data['username']!=cookie('phone')){
				$this->error('该手机号不是发送验证码的手机号');
			}
			$wechat =wechat();
			$openid = $wechat->getOpenId();
    		$status = $wechat->wechat_bind($openid,$data);

    		if ($status) {
				$this->success('绑定成功！','Member/index');
    		}else{
				$this->error('绑定失败');
    		}	
		}
		$this->assign('SEO',['title'=>'绑定微信-'.config('SITE_TITLE')]);
		$this->assign('top_title','绑定微信');
        return $this->fetch();

    }
 	public function verify(){	 	
		$captcha = new Captcha((array)Config('captcha'));
		return $captcha->entry(1);	 	
    }
 

    public function send(){

    	import('phone/ChuanglanSmsApi', EXTEND_PATH);
		$clapi = new \ChuanglanSmsApi();
		$code  = rand(456783,789561);
		$phone = input('post.telephone');
		$content = '动态码'.$code.',有效期为9分钟,请勿将动态码和密码告知他人!';
		if ( $clapi->sendSMS($phone,$content) ) {
			cookie('code',$code,900);
			cookie('phone',$phone,900);
			return true;
			
		}else{
			return false;
		}
    }

}
