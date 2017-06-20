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
namespace osc\install\controller;
use think\Controller;
class Index extends controller{

	function index(){
	
		if (is_file(APP_PATH.'database.php')) {              
              return $this->error('已经成功安装，请不要重复安装!','/');
        }
		
		return $this->fetch();
	}
	 //安装完成
    public function complete(){
        $step = session('step');

        if(!$step){
            $this->redirect('index');
        } elseif($step != 3) {
            $this->redirect("Install/step{$step}");
        }
		
        session('step', null);
        session('error', null);
       // session('update',null);
       	return $this->fetch();
    }
}
