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
use osc\common\controller\ModuleInstall;
use think\Db;
use osc\admin\service\User;
class Install extends ModuleInstall{
	
	//此操作只删除数据库表，软删除相关配置，并未删除代码，如有需要请自行处理
	function uninstall(){		
		//删除相关表,9张表
		Db::execute("DROP TABLE " 
		.config('database.prefix'). "agent," 
		.config('database.prefix'). "agent_apply," 
		.config('database.prefix'). "agent_bonus,"  
		.config('database.prefix'). "agent_cash_apply," 
		.config('database.prefix'). "agent_level,"	
		
		.config('database.prefix'). "wechat_news_reply,"		
		.config('database.prefix'). "wechat_rule,"		
		.config('database.prefix'). "wechat_share,"
		.config('database.prefix'). "wechat_text_reply"	
		);		
		//删除后台相关菜单
		Db::name('menu')->where('module','mobile')->delete();
		//删除相关模块配置
		Db::name('config')->where('module','mobile')->delete();
		
		//软删除模块表中相关信息
		Db::name('module')->where('module','mobile')->update(array('disabled'=>0));

		User::get_logined_user()->storage_user_action('删除了mobile模块');
		//清除缓存
		clear_cache();
		
		$this->success('卸载成功',url('admin/module/index'));
	}

	function install(){
		
		$module='mobile';
		
		$return=$this->create_tables($module);
		
		if(isset($return['fail'])){
			
			$this->error($return['fail']);
			
		}elseif(isset($return['success'])){
			
			include_once APP_PATH.'mobile/install/menu.php';		
			
			$this->install_module_menu($menu,0);	
						
			include_once APP_PATH.'mobile/install/config.php';			
			$this->install_module_config($config);						
						
			//更新模块表中相关信息
			Db::name('module')->where('module',$module)->update(array('disabled'=>1,'updatetime'=>date('Y-m-d',time())));

			User::get_logined_user()->storage_user_action('安装了模块'.$module);
			
			clear_cache();
			
			$this->success($return['success'],url('admin/module/index'));
		}
	}
}
