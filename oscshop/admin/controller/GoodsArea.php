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
use osc\admin\model\GoodsArea as Area;
use osc\admin\service\User;
class GoodsArea extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','商品地区');
	}
	
    public function index(){     	
		$list = Db::name('goods_area')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">~~暂无数据</td></tr>');
		$this->assign('list', $list);
	
		return $this->fetch();

	 }
	 public	function add(){
		if(request()->isPost()){	
			Db::name('goods_area')->insert(input('post.'));
			$this->success('新增成功','GoodsArea/index');
		}
		$this->assign('action', url('GoodsArea/add'));	
		$this->assign('crumbs', '新增');
		return $this->fetch('edit');
	}
	 public	function edit(){
		if(request()->isPost()){
	      $data = input('post.');
	      // var_dump($data);die;
	      Db::name('goods_area')->update(input('post.'));
	      $this->success('更新成功','GoodsArea/index');
			
		}
		$this->assign('crumbs', '修改');
		$this->assign('action', url('GoodsArea/edit'));		
		$this->assign('d',Db::name('GoodsArea')->find((int)input('id')));		
		return $this->fetch('edit');
	}
	public	function del(){
		if(request()->isGet()){	
			$r= $this->single_table_delete('GoodsArea','删除了地区');
			if($r){
				$this->redirect('GoodsArea/index');
			}
		}
	}
	
	public function autocomplete(){
				
		$filter_name=input('filter_name');
		
		if (isset($filter_name)) {			
			$sql='SELECT * FROM '.config('database.prefix')."GoodsArea where name LIKE'%".$filter_name."%' LIMIT 0,20";				
		}else{
			$sql='SELECT * FROM '.config('database.prefix')."GoodsArea  LIMIT 0,20";		
		}		
		
		$results = Db::query($sql);
		$json=[];
		foreach ($results as $result) {
				$json[] = array(					
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'GoodsArea_id' => $result['GoodsArea_id']
				);
			}
		

		return 	$json;
	}
}
