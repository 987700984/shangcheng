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
use think\Db;
use osc\common\model\Member;
use osc\member\service\User;
class Login extends APP{
	


	
	//登录验证
	public function validate_login($data){
		
			if(empty($data['telephone'])){
				$this->show('405','用户名不能为空');die;
			}elseif(empty($data['password'])){
				$this->show('405','密码不能为空');die;
			}
			$user=Db::name('member')->where('username',$data['telephone'])->find();
			if(!$user){
				$this->show('405','账号不存在');die;
			}elseif(($user['checked']==0)&&(1==config('reg_check'))){//需要审核
				$this->show('405','账号审核未通过');die;
			}
			
			if(think_ucenter_encrypt($data['password'],config('PWD_KEY'))==$user['password']){
		



				
				$login['lastdate']=time();
				$login['loginnum']		=	array('exp','loginnum+1');
				$login['lastip']	=	get_client_ip();
				$login['uid']=$user['uid'];
			$use=new Member();
			$use->update($login);
				$us=$this->hand_img($user,'userpic');
				$this->show('400','登录成功',$us);die;
			}else{
				$this->show('405','密码错误');die;
			}
	}

	
 	function login(){
	
		if(request()->isPost()){
			$data=$this->getData();

			$this->validate_login($data);


		}

    }
// 忘记密码
	public  function  forget_pw(){
		if(request()->isPost()){

			$data=$this->getData();

			if ($data['code'] != $data['vcode']) {


				$this->show('405','验证码错误');die;
			}
			$member= new Member();

			$user_info=$member->where('username',$data['telephone'])->find();


			$da= think_ucenter_encrypt($data['password'], config('PWD_KEY'));
			if ($user_info->save($da)) {

				$this->show('400','修改成功',$user_info);die;
			} else {
				$this->show('405','修改失败');die;
			}
		}

	}

	function reg(){
	
		if(request()->isPost()){

			$data=$this->getData();

			if($data['code']!=$data['vcode']){

				$this->show('405','验证码错误');die;
			}

			$result = $this->validate($data,'Member');

			if(true !== $result){

				$this->show('405',$result);die;
			}

			$member['username']=$data['telephone'];
			$member['nickname']=$data['username'];
			$member['reg_type']='mobile';
			$member['checked']=1;
			$member['telephone']=$data['telephone'];
			$member['password']=think_ucenter_encrypt($data['password'],config('PWD_KEY'));
			$member['groupid']=config('default_group_id');
			
			$member['regdate']=time();
			$member['lastdate']=time();			
			
			$member['nickname']=$data['username'];
			$member['pid']=$data['pid'];
			$uid=Db::name('member')->insert($member,false,true);
			if($uid){
				$user=Member::get($uid);
				$this->show('400','注册成功',$user);die;
			}else{
				$this->show('405','注册失败');die;
			}
			
		}

    }
    public function wei_login(){
		$data=$this->getData();
		if($user=Member::get(['wechat_openid'=>$data['wechat_openid']])){
			$login['lastdate']=time();
			$login['loginnum']		=	array('exp','loginnum+1');
			$login['lastip']	=	get_client_ip();
			$login['uid']=$user['uid'];
			$user->save($login);
			$use=Db::name('member')->where('uid',$user['uid'])->find();
			$use=$this->hand_img($use,'userpic');
			$this->show('400','登录成功',$use);die;
		}else{
			$data['reg_type']= 'mobile';
			$data['checked']=1;
			$data['groupid']=config('default_group_id');
			$data['regdate']=time();
			$data['lastdate']=time();
			$user = new Member($data);
			$user->save();
			$use=Member::get($user->uid);
			$this->show('401','登录成功',$use);die;
		}
    }

	function blind(){

		if(request()->isPost()){

			$data=$this->getData();

			if($data['code']!=$data['vcode']){

				$this->show('405','验证码错误');die;
			}

			$result = $this->validate($data,'Member');

			if(true !== $result){

				$this->show('405',$result);die;
			}

			$member['username']=$data['username'];
			$member['password']=think_ucenter_encrypt($data['password'],config('PWD_KEY'));
			$member['regdate']=time();
			$member['lastdate']=time();
			$member['pid']=$data['pid'];
			$men=Member::get($data['uid']);
			$men->save($member);
			$user=Db::name('member')->where('uid',$data['uid'])->find();
			$use=$this->hand_img($user,'userpic');
				$this->show('400','绑定成功',$use);die;


		}

	}
 

    public function send(){
		$data=$this->getData();
    	import('phone/ChuanglanSmsApi', EXTEND_PATH);
		$clapi = new \ChuanglanSmsApi();
		$code  = rand(456783,789561);
		$da=['code'=>$code,'phone'=>$data['phone']];
		$content = '动态码'.$code.',有效期为5分钟,请勿将动态码和密码告知他人!';
		if ($clapi->sendSMS($data['phone'],$content) ) {

			$this->show('400','发送成功',$da);die;
		}else {
			$this->show('405','发送失败');die;


		}
    }

}
