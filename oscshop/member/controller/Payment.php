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
namespace osc\member\controller;
use osc\common\controller\AdminBase;
use think\Db;
use osc\payment\service\Payment as PaymentService;
use osc\admin\service\User;
class Payment extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','会员');
		$this->assign('breadcrumb2','支付接口');
	}
	
    public function index()
    {  
		
		$this->assign('list',PaymentService::get_payment_code_list());
			    
		return $this->fetch();   
    }
	
	public function edit()
    {
    	
		if(request()->isPost()){	
			$payment=input('post.');			
			
			if($payment && is_array($payment)){
				$c=Db::name('config');    
	            foreach ($payment as $name => $value) {
	                $map['name']=['EQ',$name];
					$map['use_for']=['EQ','payment'];
					$c->where($map)->setField('value', $value);					
	            }				
	        }

			User::get_logined_user()->storage_user_action('修改了支付接口配置');
			
			clear_cache();
			
			return $this->success('编辑成功！',url('Payment/index'));	
		}
			
    	$list=Db::name('config')->where('extend_value',input('param.code'))->select();
		
		$this->assign('list',$list);	
		$this->assign('crumbs',input('param.code'));		    
		return $this->fetch();   
    }
	
	public function set_status(){
		
		$data=input('param.');
		
		Db::name('config')->where('extend_value',$data['code'])->update(['status'=>$data['status']],false,true);		
		
		clear_cache();

		User::get_logined_user()->storage_user_action('修改了支付接口状态');
		
		$this->redirect('Payment/index');
	}
	
	
}
