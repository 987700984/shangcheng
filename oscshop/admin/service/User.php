<?php
namespace osc\admin\service;
use think\Db;
use osc\common\model\Admin;
//后台用户数据
class User{
	
	static function is_login(){
		$user = self::get_logined_user();
		if(!empty($user)) {
			return $user->admin_id;
		}
		return 0;
	}


	/**
	 * @return null|Admin
     */
	static function get_logined_user() {
		$stored_user = session('user_auth');
		if(empty($stored_user)) {
			return null;
		} else {
			$user_id = session('user_auth_sign') == data_auth_sign($stored_user) ? $stored_user['uid'] : null;
		}

		if(!empty($user_id)) {
			$user = Admin::get($user_id);
			return $user;
		}
		return null;
	}

	static function store_logined_user($user)
	{
		session('user_auth', $user);
		session('user_auth_sign',data_auth_sign($user));
	}



	static function logout(){
		session('user_auth',null);
	}
}
