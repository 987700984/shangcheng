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
namespace osc\admin\controller;
use osc\common\controller\AdminBase;
use think\Db;
class UserAction extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','系统');
		$this->assign('breadcrumb2','用户行为');
	}
	
    public function index()
    {
    	
    	$list = Db::name('user_action')->order('ua_id desc')->paginate(config('page_num'));
		
		$this->assign('list',$list);
		    
		return $this->fetch();   
    }

	
}
