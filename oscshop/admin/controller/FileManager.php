<?php
/**
 * Waynes电子商务系统
 *
 * ==========================================================================
 * @link      http://www.waynes-tech.com
 * @copyright Copyright (c) 2015-2016 深圳市韦恩斯科技有限公司

 * ==========================================================================
 * 
 * 多用户图片管理器(只显示自己目录下的图片)
 * 
 * 图片只能一张张上传
 */
namespace osc\admin\controller;
use osc\common\controller\ImageManager;
use osc\admin\service\User;
class FileManager extends ImageManager{
	

	protected function _initialize(){	
		parent::_initialize();	
		define('UID',User::is_login());

        if(!UID) 
		exit();       
		
		$this->init('osc'.UID);
		
	}
	
}
