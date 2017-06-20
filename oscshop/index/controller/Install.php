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
 
namespace osc\index\controller;
use osc\common\controller\ModuleInstall;
use think\Db;
use osc\admin\service\User;
class Install extends ModuleInstall{
	
	//此操作软删除相关配置，并未删除代码，如有需要请自行处理
	public function uninstall(){		
		//软删除模块表中相关信息
		Db::name('module')->where('module','index')->update(array('disabled'=>0));
		User::get_logined_user()->storage_user_action('删除了index模块');
		//清除缓存
		clear_cache();		
		$this->success('卸载成功',url('admin/module/index'));
	}
	public function install(){
		$module='index';
		Db::name('module')->where('module',$module)->update(array('disabled'=>1,'updatetime'=>date('Y-m-d',time())));
		User::get_logined_user()->storage_user_action('安装了模块'.$module);
		clear_cache();		
		$this->success('安装成功',url('admin/module/index'));
	}
}
