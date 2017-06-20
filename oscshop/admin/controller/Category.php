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
use osc\admin\model\Category as CategoryModel;
use osc\admin\service\User;
use osc\common\service\Goods;
class Category extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','商品分类');
	}
	
    public function index(){
    	
		// $pid=input('param.pid');
		
		// if(!$pid){
		// 	$pid=0;
		// }
		
		$list = Db::name('category')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">~~暂无数据</td></tr>');
		$this->assign('list', $list);
		
		return $this->fetch();   
    }
	
	public function add(){
		
		if(request()->isPost()){
			
			$model=new CategoryModel();
			
			$resault=$model->add(input('post.'));
			
			if(isset($resault['error'])){
				return ['error'=>$resault['error']];
			}else{
				
				if($resault){
					User::get_logined_user()->storage_user_action('新增了商品分类');
					return ['success'=>'新增成功','action'=>'add'];				
				}else{			
					return ['error'=>'新增失败'];
				}
				
			}
			
		}
		$this->assign('category',osc_goods()->get_category_tree());
		$this->assign('action',url('Category/add'));
		$this->assign('crumbs','新增');
		return $this->fetch('edit');
	}
	
	public function edit(){

		$model=new CategoryModel();
		if(request()->isPost()){
			
			$resault=$model->edit(input('post.'));
			
			if(isset($resault['error'])){
				return ['error'=>$resault['error']];
			}else{
				
				if($resault){
					User::get_logined_user()->storage_user_action('修改了商品分类');
					return ['success'=>'修改成功','action'=>'edit'];				
				}else{			
					return ['error'=>'修改失败'];
				}
				
			}
			
		}
		
		$this->assign('category',osc_goods()->get_category_tree());
		
		$this->assign('cat',Db::name('category')->find((int)input('param.id')));
		
		$link_data=$model->category_link_data((int)input('param.id'));
		
		$this->assign('category_attribute',$link_data['attribute']);

		$this->assign('action',url('Category/edit'));
		$this->assign('crumbs','修改');
		return $this->fetch('edit');
	}
	
	//删除分类
	function del(){
		$model = new CategoryModel();
			
		$r=$model->del_category((int)input('param.id'));
		
		if($r){

			User::get_logined_user()->storage_user_action('删除了分类'.input('get.id'));
			
			$this->redirect('Category/index');
			
		}else{
			
			return $this->error('删除失败！',url('Category/index'));
		}		
		
	}
	
	function autocomplete(){	
		
		$filter_name=input('filter_name');
		
		if (isset($filter_name)) {
			$sql='SELECT id,name FROM '.config('database.prefix')."category where name LIKE'%".$filter_name."%' LIMIT 0,20";
		}else{
			$sql='SELECT id,name FROM '.config('database.prefix')."category WHERE pid=32  LIMIT 0,20";
		
		}
		$results = Db::query($sql);
		$json=[];
		foreach ($results as $result) {
			$json[] = array(
				'category_id' => $result['id'],
				'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
			);
		}
		return 	$json;
	}
	function autocompletet(){	
		
		$filter_name=input('filter_name');
		
		if (isset($filter_name)) {
			$sql='SELECT id,name FROM '.config('database.prefix')."category where name LIKE'%".$filter_name."%' LIMIT 0,20";
			$results = Db::query($sql);
		}else{
			$results = osc_goods()->get_category_tree();
		
		}
		$json=[];
		foreach ($results as $result) {
			
				if ($result['pid']!==0 && $result['pid']!==37) {
					$json[] = array(
					'category_id' => $result['id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
					);	
				}
				
		}
		// dump($json);die;
		return 	$json;
	}
	//更新排序
	function update_sort(){
		$data=input('post.');
		
		$update['id']=(int)$data['cid'];
		$update['sort_order']=(int)$data['sort'];
		
		if(Db::name('category')->update($update)){

			User::get_logined_user()->storage_user_action('更新了分类排序');
			
			return true;
		}		
	}
}
