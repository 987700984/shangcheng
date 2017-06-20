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
namespace osc\common\controller;
use think\Controller;
use think\Db;
use osc\admin\service\User;
abstract class ModuleInstall extends controller{
	
	protected function _initialize() {		
		
		if (!is_file(APP_PATH.'database.php')) {
			header('Location:'.request()->domain().'/install');
			die();
		}				
				
		$config =   cache('db_config_data');
		
        if(!$config){        	
            $config =   load_config();					
            cache('db_config_data',$config);
        }
		
        config($config); 
		
		define('UID',User::is_login());

        if(!UID){  
			$this->redirect('admin/Login/login');
        }
		
		if(session('user_auth.username')!=config('administrator')){
		 	$this->error('请使用超级管理员账号进行操作！！');
		}
	}

	/**
	 * 创建数据表
	 * @param  $module 模块
	 */
	protected function create_tables($module)
	{
		$sql_file=APP_PATH.$module.'/install/install.sql';

		//没有安装文件
		if(!is_file($sql_file)){
			return ['fail'=>'失败'];
		}

		//读取SQL文件
		$sql = file_get_contents($sql_file);
		$sql = str_replace("\r", "\n", $sql);
		$sql = explode(";\n", $sql);
		//替换表前缀
		$orginal = 'osc_';
		$prefix=config('database.prefix');
		$sql = str_replace(" `{$orginal}", " `{$prefix}", $sql);
		//开始安装
		foreach ($sql as $value) {
			$value = trim($value);
			if (empty($value)) continue;
			//创建数据表
			if (substr($value, 0, 12) == 'CREATE TABLE') {
				if (false== Db::execute($value)) {
					return ['fail'=>'失败'];
				}
			} else {
				Db::execute($value);
			}
		}

		return ['success'=>'安装成功'];
	}
	
	
	//安装模块配置
	public function install_module_config($data) {
		
		foreach ($data as $k => $d) {
			
			$config['name']=$d['name'];
			$config['value']=$d['value'];
			$config['info']=$d['info'];
			$config['module']=$d['module'];
			$config['module_name']=$d['module_name'];
			$config['extend_value']=$d['extend_value'];
			$config['use_for']=$d['use_for'];
			$config['status']=$d['status'];
			$config['sort_order']=$d['sort_order'];
			
            Db::name('config')->insert($config,false,true);
		}
		
	}
	
	//安装模块菜单
	public function install_module_menu($data,$pid) {
		
        if (empty($data) || !is_array($data)) {            
			return false;
        }        
       
        foreach ($data as $d) {
            
			$menu['module']=$d['module'];
			$menu['pid']=$pid;
			$menu['title']=$d['title'];
			$menu['url']=$d['url'];
			$menu['icon']=$d['icon'];
			$menu['sort_order']=$d['sort_order'];
			$menu['type']=$d['type'];
			$menu['status']=$d['status'];
			
            $newId = Db::name('menu')->insert($menu,false,true);
            //是否有子菜单
            if (!empty($d['children'])) {
                if ($this->install_module_menu($d['children'],$newId) !== true) {
                    return false;
                }
            }
        }
        return true;
    }
	
	//必须实现安装
    abstract public function install();

    //必须实现卸载
    abstract public function uninstall();
	
}
