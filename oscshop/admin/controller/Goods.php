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
use osc\admin\model\Goods as GoodsModel;
use osc\admin\service\User;
class Goods extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','入会商品');
		$this->assign('breadcrumb2','入会商品管理');
	}
	//商品列表
    public function index(){
    	
		$filter=input('param.');
        
		if(isset($filter['type'])&&$filter['type']=='search'){
			$list=osc_goods()->get_category_goods_list($filter,config('page_num'),0);
		}else{
			$list=Db::name('goods')->where('is_points_goods','0')->order('goods_id desc')->paginate(config('page_num'));
		}		

		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		
		$this->assign('category',Db::name('category')->field('id,pid,name')->where('pid','32')->select());
		
		$this->assign('list',$list);
	
		return $this->fetch();

	 }
	 //新增商品
	 public function add(){
		
		if(request()->isPost()){
			
			$data=input('post.');
			// dump($data);die;
			$model=new GoodsModel();
			
			$error=$model->validate($data);	
	
			if($error){					
				$this->error($error['error']);	
			}

			$data['postage'] = $data['postage']==''?null:$data['postage'];
			
			$return=$model->add_goods($data);		
			
			if($return){

				User::get_logined_user()->storage_user_action('新增了商品');
				$this->success('新增成功！',url('Goods/index'));			
			}else{
				$this->error('新增失败！');
			}
		}
		$this->assign('area',Db::name('goods_area')->select());
		$this->assign('weight_class',Db::name('WeightClass')->select());
		$this->assign('length_class',Db::name('LengthClass')->select());
	 	$this->assign('crumbs', '新增');
		$this->assign('action', url('Goods/add'));
		
	 	return $this->fetch('edit');
	 }
	 //商品基本信息
	 public function edit_general(){
	 	
		if(request()->isPost()){
			
			$data=input('post.');
			
			if(empty($data['name'])){
		
				$this->error('商品名称必填！');	
			}
			$description=$data['description'];
			unset($data['description']);
			$data['postage'] = $data['postage']==''?null:$data['postage'];

			try{
				
				Db::name('goods')->update($data,false,true);
				Db::name('goods_description')->where('goods_id',$data['goods_id'])->update($description,false,true);
				User::get_logined_user()->storage_user_action('更新商品基本信息');
				return $this->success('更新成功！',url('Goods/index'));
				
			}catch(Exception $e){
				return $this->error('更新失败！'.$e);	
			}
			
		}
		
		$this->assign('area',Db::name('goods_area')->select());
		$this->assign('category',Db::name('category')->where('pid',32)->select());
		$this->assign('weight_class',Db::name('WeightClass')->select());
		$this->assign('length_class',Db::name('LengthClass')->select());
		$this->assign('description',Db::name('goods_description')->where('goods_id',(int)input('id'))->find());
	 	$this->assign('goods',Db::view(['Goods','o'],'*')->view('GoodsToCategory','category_id','GoodsToCategory.goods_id=o.goods_id')->where('o.goods_id',input('param.id'))->find());
		
	 	$this->assign('crumbs', '编辑基本信息');	
		
	 	return $this->fetch('general');
	 }
	 //商品关联项	
	 public function edit_links(){
		 $model = new GoodsModel();
	 	
		if(request()->isPost()){				
				
				$resault=$model->edit_links(input('post.'));
					
				if($resault){
					User::get_logined_user()->storage_user_action('更新商品分类');
					return $this->success('更新成功！',url('Goods/index'));
				}else{
					return $this->error('更新失败！');
				}				
		
		}
		
		$link_data=$model->get_link_data((int)input('param.id'));
	 	$this->assign('goods_categories',$link_data['goods_categories']);
		
		$this->assign('goods_attribute',$link_data['goods_attribute']);

	 	$this->assign('crumbs', '关联');	
		
	 	return $this->fetch('links');
	 }
	 //商品选项
	 public function edit_option(){
	 	
		if(request()->isPost()){
				
			$data=input('post.');
			
			if (isset($data['goods_option'])) {
				foreach ($data['goods_option'] as $goods_option) {
					
					if(!isset($goods_option['goods_option_value'])){					
						$this->error('选项值必填');
					}
								
					foreach ($goods_option['goods_option_value'] as $k => $v) {
						if((int)$v['quantity']<=0){
							$this->error('数量必填');
						}
					}
				}
			}
			$model=new GoodsModel();
			
			$model->edit_option($data);

			User::get_logined_user()->storage_user_action('更新商品选项');
									
			return $this->success('更新成功！',url('Goods/index'));

		}		
		
		$goods_options=osc_goods()->get_goods_options(input('id'));
		
		$this->assign('goods_options',$goods_options);	

		//选项值
		$option_values=[];
		foreach ($goods_options as $goods_option) {
				$option_values[$goods_option['option_id']] = osc_goods()->get_option_values($goods_option['option_id']);
		}		
		
		$this->assign('option_values',$option_values);	
		
		$this->assign('crumbs', '选项');	
	 	return $this->fetch('option');
	 }
	 //商品折扣
	 public function edit_discount(){		
		
		$this->assign('goods_discount',Db::name('goods_discount')->where('goods_id',input('id'))->order('quantity ASC')->select());	
		$this->assign('crumbs', '折扣');	
	 	return $this->fetch('discount');
	 }
	 //商品相册
	 public function edit_image(){
	 	$this->assign('goods_images',Db::name('goods_image')->where('goods_id',input('id'))->order('sort_order asc')->select());	
		$this->assign('crumbs', '商品相册');	
	 	return $this->fetch('image');
	 }
	 //商品手机端描述
	 public function edit_mobile(){
	 	$this->assign('mobile_images',Db::name('goods_mobile_description_image')->where('goods_id',input('id'))->order('sort_order asc')->select());	
		$this->assign('crumbs', '手机端描述');	
	 	return $this->fetch('mobile');
	 }
	 
	//编辑信息，新增，修改
	function ajax_eidt(){
		if(request()->isPost()){
			
			$data=input('post.');
			
			$table_name=$data['table'];
			
			if(isset($data[$table_name][$data['key']])){
				$info=$data[$table_name][$data['key']];
			}	
			
			if($table_name=='goods_discount'){
				if(!is_numeric($info['quantity'])||!is_numeric($info['price']))
				return ['error'=>'请输入数字'];
			}
			
			if(isset($data['id'])&&$data['id']!=''){
				//更新
				$info[$data['pk_id']]=(int)$data['id'];				
								
				$r=Db::name($table_name)->update($info,false,true);
				if($r){
					User::get_logined_user()->storage_user_action('更新商品'.$data['id']);
					return ['success'=>'更新成功'];
				}else{
					return ['error'=>'更新失败'];
				}
			}else{
				//新增
				$info['goods_id']=(int)$data['goods_id'];
		
				$r=Db::name($table_name)->insert($info,false,true);
				if($r){
					User::get_logined_user()->storage_user_action('更新商品'.$data['goods_id']);
					return ['success'=>'更新成功','id'=>$r];
				}else{
					return ['error'=>'更新失败'];
				}
			}
		}
	}
	//用于编辑中删除
	 function ajax_del(){
		if(request()->isPost()){
			$data=input('post.');		
			
			if(empty($data['id'])){
				return ['success'=>'删除成功'];
			}
			
			$r=Db::name($data['table'])->delete($data['id']);
			
			if($r){
				return ['success'=>'删除成功'];
			}else{
				return ['error'=>'删除失败'];
			}
		}
	}
	//复制商品 
	function copy_goods(){
		$id =input('post.');

		$model=new GoodsModel();
		 	
		if($id){		
			foreach ($id['id'] as $k => $v) {						
				$model->copy_goods((int)$v);
			}
			User::get_logined_user()->storage_user_action('复制商品');
			
			$data['redirect']=url('Goods/index');	
						
			return $data;
		}
	}
	//删除商品
	function del(){
		
		$model=new GoodsModel();
			
		$r=$model->del_goods((int)input('param.id'));	
		
		if($r){

			User::get_logined_user()->storage_user_action('删除商品'.input('get.id'));
			
			$this->redirect('Goods/index');
			
		}else{
			
			return $this->error('删除失败！',url('Goods/index'));
		}		
		
	}
	//更新状态
	function set_status(){
		$data=input('param.');
		
		$update['goods_id']=(int)$data['id'];
		$update['status']=(int)$data['status'];
		
		if(Db::name('goods')->update($update)){
			User::get_logined_user()->storage_user_action('更新商品状态');
			$this->redirect('Goods/index');
		}
	}	
	//更新价格
	function update_price(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['price']=(float)$data['price'];
		
		if(Db::name('goods')->update($update)){
			User::get_logined_user()->storage_user_action('更新商品价格');
			return true;
		}		
	}
	//更新价格
	function update_origin_price(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['origin_price']=(float)$data['origin_price'];
		
		if(Db::name('goods')->update($update)){
			User::get_logined_user()->storage_user_action('更新商品价格');
			return true;
		}		
	}
	//更新数量
	function update_quantity(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['quantity']=(int)$data['quantity'];
		
		if(Db::name('goods')->update($update)){
			User::get_logined_user()->storage_user_action('更新商品数量');
			return true;
		}		
	}
	//更新排序
	function update_sort(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['sort_order']=(int)$data['sort'];
		
		if(Db::name('goods')->update($update)){
			User::get_logined_user()->storage_user_action('更新商品排序');
			return true;
		}		
	}
    //选择产品是否是热卖Lavender
    public function ifhot(){
        if(Request::instance()->isGet()){
            $id = Request::instance()->param('id');
            $result   = Db::name('goods')->field('osc_goods.hot')->where('goods_id='.$id)->find();
            if($result['hot']=='no'){
                $hot['hot']='yes';
                $r    = Db::name('goods')->where('goods_id='.$id)->update($hot);
                if($r){
                    $hot = 'yes';
                    return $hot;
                }
            }
            if($result['hot']=='yes'){
                $hot['hot']='no';
                $r    = Db::name('goods')->where('goods_id='.$id)->update($hot);
                if($r){
                    $hot = 'no';
                    return $hot;
                }
            }
        }
    }

    //选择产品是否是最新Lavender
    public function ifnew(){
        if(request()->isGet()){
            $id = request()->param('id');
            $result   = Db::name('goods')->field('osc_goods.new')->where('goods_id='.$id)->find();
            if($result['new']=='no'){
                $hot['new']='yes';
                $r    = Db::name('goods')->where('goods_id='.$id)->update($hot);
                if($r){
                    User::get_logined_user()->storage_user_action('选择商品推荐属性');
                    $new = 'yes';
                    return $new;
                }
            }
            if($result['new']=='yes'){
                $hot['new']='no';
                $r    = Db::name('goods')->where('goods_id='.$id)->update($hot);
                if($r){
                    User::get_logined_user()->storage_user_action('取消商品推荐属性');
                    $new = 'no';
                    return $new;
                }
            }
        }
    }

    //批量操作：商品上架下架Laverder
    public function operation(){
        if(request()->isPost()){
            $data  = input('post.');
            foreach($data['id'] as $key =>$value){
                $id    = $value;
                if($data['operation']=='off'){//下架status=2；
                    $r[] = Db::name('goods')->where('goods_id='.$id)->update(['status'=>2]);
                }
                if($data['operation']=='on'){//上架status=1;
                    $r[] = Db::name('goods')->where('goods_id='.$id)->update(['status'=>1]);
                }
            }
            if($r){
                User::get_logined_user()->storage_user_action('商品批量上架');
                return 1;
            }else{
                return 2;
            }
        }
    }
	
}
