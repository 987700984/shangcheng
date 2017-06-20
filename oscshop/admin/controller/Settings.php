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
use osc\admin\service\User;
use osc\common\model\Address;
use osc\common\model\Admin;

class Settings extends AdminBase{

	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','系统');

	}

//自提地址修改
	public function general(){
		if (request()->isPost()) {
			$address = new Address();
			$edit    = $address->save(input('post.'),input('post.address_id'));
			if ($edit) {
				$this->success('修改成功！','Settings/general_list');
			}else{
				$this->error('修改失败！');
			}
		}else{
			if (input('param.id')) {
				$this->assign('address',Address::get(input('param.id')));
				$this->assign('status','edit');
				return $this->fetch();
			}else{
				$this->error('非法操作！');
			}
		}
	}
//自提地址删除
	public function del(){
			if (input('param.id')) {
				$del = Address::destory(input('param.id'));
				if ($del) {
					$this->success('删除成功！','Settings/general_list');
				}else{
					$this->error('删除失败！');
				}
			}else{
				$this->error('非法操作！');
			}
	}
//自提地址新增
	public function add(){
		if (request()->isPost()) {
			$address = new Address();
			$edit    = $address->save(input('post.'));
			if ($edit) {
				$this->success('新增成功！','Settings/general_list');
			}else{
				$this->error('新增失败！');
			}
		}else{
			$this->assign('status','add');
			return $this->fetch('general');
		}



		
	}
//自提地址列表
	function general_list(){
		$address= new Address;
		$this->assign('list',$address->where('uid',0)->paginate(config('page_num')));
		return $this->fetch();
	}














	function get_config_by_module($module){

		$list=Db::name('config')->where('module',$module)->select();
		if(isset($list)){
			foreach ($list as $k => $v) {
				$config[$v['name']]=$v;
			}
		}
		return $config;
	}

	function save(){

		if(request()->isPost()){

			$config=input('post.');

			if($config && is_array($config)){
				$c=Db::name('Config');
	            foreach ($config as $name => $value) {
	                $map = array('name' => $name);
					$c->where($map)->setField('value', $value);
	            }

	        }
	        clear_cache();
			User::get_logined_user()->storage_user_action('更新系统基本配置');
	      return ['success'=>'更新成功'];

		}
	}

	function other(){

		$this->assign('length',Db::name('length_class')->select());
		$this->assign('weight',Db::name('weight_class')->select());
		$this->assign('order_status',Db::name('order_status')->select());
		$this->assign('member_auth_group',Db::name('member_auth_group')->field('id,title')->select());
		$this->assign('breadcrumb2','其他配置');

		return $this->fetch();
	}



	function password(){
		$admin = Admin::get(['admin_id'=>1]);
		if(request()->isPost()){
			if(input('password')!=input('repassword')){
				$this->error('两次密码不一致');
			}else{
				$pwd = think_ucenter_encrypt(input('password'),config('PWD_KEY'));
			$admin->passwd = $pwd;
				$admin->save();
				clear_cache();
				User::get_logined_user()->storage_user_action('更改了管理员密码');

				User::get_logined_user()->storage_user_action('退出了系统');
				User::logout();
				return $this->success('修改成功');

			}

		}else{
			$this->assign('admin',$admin);
			return $this->fetch();
		}
	}

}
