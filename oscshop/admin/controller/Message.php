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
use \think\Db;
use think\Request;
use \think\Session;
use osc\common\controller\AdminBase;
use osc\admin\service\User;

class Message extends AdminBase{

	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','文章');
		$this->assign('breadcrumb2','文章管理');
	}

	public function index(){
    	
		
		
		
		$list = Db::view('message','*')->view('admin','user_name','message.author=admin.admin_id')->view('message_category','name','message_category.id=message.category')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">~~暂无数据</td></tr>');
		$this->assign('list', $list);
		
		return $this->fetch();   
    }
	
	public function add(){
		
		if(request()->isPost()){
			$data=input('param.');

			$data['time']=time();
			$data['author'] = UID;
			$resault = Db::name('message')->insert($data);
				if($resault){
					User::get_logined_user()->storage_user_action('新增了文章');
					$this->success('新增成功','Message/index');				
				}else{			
					$this->error('新增失败');
				}
				
			
			
		}
		$this->assign('category',Db::name('message_category')->select());
		$this->assign('action',url('Message/add'));
		$this->assign('crumbs','新增');
		return $this->fetch('edit');
	}
	//修改文章	
	public function edit(){

		if(request()->isPost()){
			$data=input('param.');
			$data['time'] = time();
			$data['author'] = UID;
			$resault = Db::name('message')->update($data);
				
				if($resault){
					User::get_logined_user()->storage_user_action('修改了文章分类');
					$this->success('修改成功','Message/index');					
				}else{			
					$this->error('修改失败');
				}
				
			
			
		}

		$this->assign('category',Db::name('message_category')->select());
		$this->assign('mes',Db::name('message')->where('id',input('param.id'))->find());
		$this->assign('action',url('Message/edit'));
		$this->assign('crumbs','修改');
		return $this->fetch();
	}
	//文章分类列表
	public function category(){
		$list = Db::name('message_category')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">~~暂无数据</td></tr>');
		$this->assign('list', $list);
		
		return $this->fetch();
	}

	//修改文章分类	
	public function category_edit(){

		if(request()->isPost()){
			
			$resault = Db::name('message_category')->update(input('param.'));
				
				if($resault){
					User::get_logined_user()->storage_user_action('修改了文章分类');
					return ['success'=>'修改成功','action'=>'edit'];				
				}else{			
					return ['error'=>'修改失败'];
				}
				
			
			
		}
		
		$this->assign('cat',Db::name('message_category')->where('id',input('param.id'))->find());
		$this->assign('action',url('Message/category_edit'));
		$this->assign('crumbs','修改');
		return $this->fetch();
	}
	
	//删除文章
	function del(){
		
		$r=Db::name('message')->where('id',input('param.id'))->delete();	
		
		
		if($r){

			User::get_logined_user()->storage_user_action('删除了文章'.input('get.id'));
			
			$this->redirect('Message/index');
			
		}else{
			
			return $this->error('删除失败！',url('Message/index'));
		}		
		
	}





}
