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
use osc\common\model\Product as Pro;
use osc\admin\service\User;
class Product extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','产品入会列表');
		$this->assign('breadcrumb2','产品入会管理');
	}

	public function index(){
		$filter=input('param.');
        
		if(isset($filter['type'])&&$filter['type']=='search'){
			$like='%'.input('param.name').'%';
			$list=Db::name('product')->where('name','like',$like)->order('id desc')->paginate(config('page_num'));
		}else{
			$list=Db::name('product')->order('id desc')->paginate(config('page_num'));
		}
		$this->assign('empty','暂时没有内容');
		$this->assign('list',$list);
		return $this->fetch();
	}

	public function image(){
		if (request()->isGet()) {
			$pro= Pro::get(input('param.id'));
			$img=$pro->images()->select();
			$this->assign('image',$img);
		}
		return $this->fetch();
	}

//会员充值
	public function recharge(){
		if (request()->isPost()) {
			$phone= Pro::get(input('post.id'));
			$re = Db::name('member')->where('uid',$phone['uid'])->setInc('cash_points',input('post.points'));
			$re = Db::name('product')->where('id',input('post.id'))->update(['record_time'=>date('Y-m-d,H:i:s'),'status'=>1]);
			if ($re) {
				$this->success('充值成功！','Product/index');
			}
		}else{
			if (request()->isGet()) {
				$this->assign('id',input('param.id'));
				return $this->fetch();
			}
			
		}
		
	}


}
