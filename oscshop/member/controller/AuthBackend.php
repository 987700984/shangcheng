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
 * 会员权限管理
 */
namespace osc\member\controller;
use osc\common\controller\AdminBase;
use think\Db;
use osc\admin\service\User;
class AuthBackend extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','会员');
		$this->assign('breadcrumb2','权限管理');
	}
	
     public function index(){
     	
     	$list = Db::name('member_auth_group')->paginate(config('page_num'));
		
		$this->assign('list',$list);
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
    	return $this->fetch();
	 }
	
	public function create_group(){

		if(request()->isPost()){			  
			return $this->single_table_insert('MemberAuthGroup','添加了会员用户组');
		}
		
		$this->assign('breadcrumb2','新增用户组');
		$this->assign('action',url('AuthBackend/create_group'));
		return $this->fetch('edit_group');
		
	}
	
	function edit_group(){
		if(request()->isPost()){
			return $this->single_table_update('MemberAuthGroup','修改了会员用户组');
		}	
		
		$this->assign('group',Db::name('member_auth_group')->find(input('id')));
		$this->assign('breadcrumb2','编辑');
		$this->assign('action',url('AuthBackend/edit_group'));
		return $this->fetch('edit_group');		
	}
	
	public function del_group(){
		User::get_logined_user()->storage_user_action('删除了会员用户组');
		Db::name('member_auth_group')->delete(input('id'));
		$this->redirect('AuthBackend/index');		
	}

	

	public function set_status(){
		$data=input('param.');
		
		Db::name('member_auth_group')->where('id',$data['id'])->update(['status'=>$data['status']],false,true);

		User::get_logined_user()->storage_user_action('修改了会员用户组状态');
		
		$this->redirect('AuthBackend/index');
	}
}
