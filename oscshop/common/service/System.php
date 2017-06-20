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
 * 系统公共数据获取
 * 
 */
namespace osc\common\service;
use think\Db;
class System{	
	
	/**
     * object 对象实例
     */
    private static $instance;
	
	//禁外部实例化
	private function __construct(){}
	
	//单例模式	
	public static function getInstance(){    
        if (!(self::$instance instanceof self))  
        {  
            self::$instance = new self();  
        }  
        return self::$instance;  
    }
	//禁克隆
	private function __clone(){} 
    
	//取得系统配置分组列表
	public function get_config_module(){
	 	
		if (!$config_module = cache('config_module')) {
		
			$list=Db::name('config')->field('module,module_name')->group('module,module_name')->select();
			
			cache('config_module', $list);	
			
			$config_module=$list;
		}
		return $config_module;		
	}	

}
