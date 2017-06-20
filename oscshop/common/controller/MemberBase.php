<?php
/**
 *
 * @author    深圳韦恩斯科技有限公司
 *会员中心
 */
namespace osc\common\controller;
use think\Db;
use osc\member\service\User;
class MemberBase extends Base{	
	
	protected function _initialize() {
		parent::_initialize();		
		
		define('UID',User::is_login());
		
		if(!UID){
			
			 $this->redirect('member/login/user_login');

		}			
		
		$this->get_menu();
		

	}
	
	public function get_menu(){

		$parent_menu =
			array (
				0 =>
					array (
						'id' => 19,
						'module' => 'member',
						'pid' => 0,
						'title' => '我的订单',
						'url' => '',
						'icon' => 'fa-credit-card fa-lg',
						'sort_order' => 0,
						'type' => 'nav',
						'children' =>
							array (
								0 =>
									array (
										'id' => 21,
										'module' => 'member',
										'pid' => 19,
										'title' => '订单管理',
										'url' => 'member/order_member/index',
										'icon' => '',
										'sort_order' => 1,
										'type' => 'nav',
									),
							),
					),
				1 =>
					array (
						'id' => 13,
						'module' => 'member',
						'pid' => 0,
						'title' => '个人资料',
						'url' => '',
						'icon' => 'fa-users fa-lg',
						'sort_order' => 1,
						'type' => 'nav',
						'children' =>
							array (
								0 =>
									array (
										'id' => 14,
										'module' => 'member',
										'pid' => 13,
										'title' => '我的资料',
										'url' => 'member/account/profile',
										'icon' => '',
										'sort_order' => 1,
										'type' => 'nav',
									),
								1 =>
									array (
										'id' => 15,
										'module' => 'member',
										'pid' => 13,
										'title' => '修改密码',
										'url' => 'member/account/password',
										'icon' => '',
										'sort_order' => 2,
										'type' => 'nav',
									),
								2 =>
									array (
										'id' => 20,
										'module' => 'member',
										'pid' => 13,
										'title' => '地址簿',
										'url' => 'member/account/address',
										'icon' => '',
										'sort_order' => 3,
										'type' => 'nav',
									),
							),
					),
			);

		$this->assign('admin_menu',$parent_menu);
	
		
	}
	


	
}
