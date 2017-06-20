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
use osc\common\model\Brand as BrandModel;
use osc\admin\service\User;
class Brand extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','品牌');
	}
	
    public function index(){     	
		
		$list = Db::name('brand')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">~~暂无数据</td></tr>');
		$this->assign('list', $list);
	
		return $this->fetch();

	 }
	 public	function add(){
		if(request()->isPost()){	
			return $this->single_table_insert('Brand','添加了作者');
		}
		$this->assign('crumbs', '新增');
		$this->assign('action', url('Brand/add'));
		return $this->fetch('edit');
	}
	 public	function edit(){
		if(request()->isPost()){
$data = input('post.');

			if(isset($data['brand_id'])&&$data['brand_id']>0){
				$brand = BrandModel::get(['brand_id'=>$data['brand_id']]);

				$result = $brand->allowField(true)->save($data);
			}else{
				$brand = new BrandModel();

				$result = $brand->allowField(true)->save($data);

				User::get_logined_user()->storage_user_action('添加/修改了一个作者');
			}


$this->success('操作成功');
//			return $this->single_table_update('Brand','修改了作者');
		}
		$this->assign('crumbs', '修改');
		$this->assign('action', url('Brand/edit'));		
		$this->assign('d',Db::name('Brand')->find((int)input('id')));		
		return $this->fetch('edit');
	}
	public	function del(){
		if(request()->isGet()){	
			$r= $this->single_table_delete('Brand','删除了作者');
			if($r){
				$this->redirect('Brand/index');
			}
		}
	}
	
	public function autocomplete(){
				
		$filter_name=input('filter_name');
		
		if (isset($filter_name)) {			
			$sql='SELECT * FROM '.config('database.prefix')."brand where name LIKE'%".$filter_name."%' LIMIT 0,20";				
		}else{
			$sql='SELECT * FROM '.config('database.prefix')."brand  LIMIT 0,20";		
		}		
		
		$results = Db::query($sql);
		$json=[];
		foreach ($results as $result) {
				$json[] = array(					
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'brand_id' => $result['brand_id']
				);
			}
		

		return 	$json;
	}
}
