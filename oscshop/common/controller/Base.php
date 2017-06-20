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
class Base extends controller{
	
	protected function _initialize() {		

		if (!is_file(APP_PATH.'database.php')) {
			header('Location:'.request()->domain().'/install');
			die();
		}				

		$module=request()->module();
		
		if(!is_module_install($module)){
			die('该模块未安装');
		}
		
		$config =   cache('db_config_data');
		
        if(!$config){        	
            $config =   load_config();					
            cache('db_config_data',$config);
        }
		
        config($config);
	}
	
}
