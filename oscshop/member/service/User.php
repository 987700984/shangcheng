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
namespace osc\member\service;
use think\Db;
use osc\common\model\Member;
use osc\common\model\JournalAccount;
//用户数据
class User{
	
	static function is_login(){
				
		$user = self::get_logined_user();
	    if (empty($user)) {
	        return 0;
	    } else {
			return $user->uid;
	    }
	}

	static function store_logined_user($user)
	{
		if('session'==config('member_login_type')){
			session('member_user_auth', $user);
			session('member_user_auth_sign',data_auth_sign($user));
		}elseif('cookie'==config('member_login_type')){
			cookie('member_user_auth',$user,3600*7);
			cookie('member_user_auth_sign',data_auth_sign($user),3600*7);
		}
	}


	/**
	 * @return null|Member
     */
	static function get_logined_user()
	{
		if('session'==config('member_login_type')){
			$stored_user = session('member_user_auth');
			$user_auth_sign = session('member_user_auth_sign');
		}
		else{
			$stored_user = cookie('member_user_auth');
			$user_auth_sign = cookie('member_user_auth_sign');
		}

		if (empty($stored_user)) {
			return null;
		} else {
			$user_id = $user_auth_sign == data_auth_sign($stored_user) ? $stored_user['uid'] : null;
		}

		if(!empty($user_id)) {
			$user = Member::get($user_id);
			return $user;
		}
		return null;
	}
	
	static function logout(){
		
		if('session'==config('member_login_type')){
			session('member_user_auth',null);			
		}elseif('cookie'==config('member_login_type')){
			cookie('member_user_auth', null);			
		}
	}
	



	//取得会员所有收货地址
	static function get_address($uid) {
		
		if(!isset($uid)){
			return false;
		}
		
		$area_id=Db::query("SELECT DISTINCT province_id,city_id,country_id FROM ".config('database.prefix')."address WHERE uid=".$uid);
		
		foreach ($area_id as $k => $v) {
			foreach ($v as $key => $value) {
				$area[]=$value;
			}
		}
		
		if(!isset($area)){
			return;
		}
	
		//地区的id,去除重复的
		$arr=array_unique($area);
		$aid=implode(',',$arr);

		//地区的名字
		$area_name=Db::query("SELECT area_name,area_id FROM ".config('database.prefix')."area WHERE area_id IN (".$aid.")");
	
		//取得会员的所有地址
		$address=Db::name('address')->where('uid',$uid)->select();
		
		foreach ($address as $key => $v) {
			$a[$v['address_id']]=$v;
		}
	
		foreach ($a as $k => $v) {
			
			foreach ($area_name as $key => $value) {
				if($v['province_id']==$value['area_id']){
					$a[$k]['province']=$value['area_name'];
				}
				if($v['city_id']==$value['area_id']){
					$a[$k]['city']=$value['area_name'];
				}
				if($v['country_id']==$value['area_id']){
					$a[$k]['country']=$value['area_name'];
				}
			}
			
		}
		return $a;		
	}

	//新增收货地址
	static function add_address($data){
		//写入地址表
		$address['uid']=member('uid');
					
		$address['name']=$data['name'];
		$address['telephone']=$data['telephone'];
		
		$address['address']=$data['address'];	
		
		$address['city_id']=$data['city_id'];
		$address['country_id']=$data['country_id'];
		$address['province_id']=$data['province_id'];
		
		$address_id=Db::name('address')->insert($address,false,true);		
		//会员表更新地址
		if($address_id){
			$member['address_id']=$address_id;
			$member['uid']=member('uid');
			Db::name('member')->update($member);
		}
		self::get_logined_user()->storage_user_action('新增了收货地址');
		return $address_id;
	}


	static function log_journal_account($uid,$type='charge'){
switch($type){
	case 'charge':break;
	case 'frozen':


}


	}
}
