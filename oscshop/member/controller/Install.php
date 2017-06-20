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
 
namespace osc\member\controller;
use osc\common\controller\ModuleInstall;
use think\Db;
use osc\admin\service\User;
class Install extends ModuleInstall{
	
	//此操作只删除数据库表，软删除相关配置，并未删除代码，如有需要请自行处理
	public function uninstall(){
		//删除相关表,16张表
		Db::execute("DROP TABLE " 
		.config('database.prefix'). "member," 
		.config('database.prefix'). "member_auth_group," 
		.config('database.prefix'). "member_wishlist,"
	
		.config('database.prefix'). "address,"		
		.config('database.prefix'). "cart,"
		.config('database.prefix'). "transport,"
		.config('database.prefix'). "transport_extend,"
		.config('database.prefix'). "order,"
		.config('database.prefix'). "order_goods,"
		.config('database.prefix'). "order_history,"
		.config('database.prefix'). "order_option,"
		.config('database.prefix'). "order_status,"
		.config('database.prefix'). "blacklist"
		);
		//软删除后台相关菜单
		Db::name('menu')->where('module','member')->update(array('status'=>0));
		//软删除相关模块配置
		Db::name('config')->where('module','member')->update(array('status'=>0));
		//软删除模块表中相关信息
		Db::name('module')->where('module','member')->update(array('disabled'=>0));

		User::get_logined_user()->storage_user_action('删除了member模块');
		//清除缓存
		clear_cache();
		
		$this->success('卸载成功',url('admin/module/index'));
	}

	public function install(){
		
		$module='member';
				
		$return=$this->create_tables($module);
		
		if(isset($return['fail'])){
			
			$this->error($return['fail']);
			
		}elseif(isset($return['success'])){
			
			//更新相关菜单
			if(Db::name('menu')->where('module',$module)->select()){
				Db::name('menu')->where('module',$module)->update(array('status'=>1));
			}
			
			//更新相关系统配置
			if(Db::name('config')->where('module',$module)->select()){
				Db::name('config')->where('module',$module)->update(array('status'=>1));
			}
						
			//更新模块表中相关信息
			Db::name('module')->where('module',$module)->update(array('disabled'=>1,'updatetime'=>date('Y-m-d',time())));

			User::get_logined_user()->storage_user_action('安装了模块'.$module);
			
			clear_cache();
			
			$this->success($return['success'],url('admin/module/index'));
		}
	}


}
