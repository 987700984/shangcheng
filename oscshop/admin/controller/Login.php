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
use osc\common\controller\Base;
use think\Db;
use think\Cookie;
use osc\admin\service\User;
class Login extends Base{
	
	public function login(){

		 if(request()->isPost()){
			
			$data=input('post.');
			// var_dump(think_ucenter_encrypt($data['password'],config('PWD_KEY')));
			if(empty($data['username'])){
				$this->error('用户名不能为空！');
			}elseif(empty($data['password'])){
				$this->error('密码不能为空！');
			}
			$user_info=Db::name('admin')->where('user_name',$data['username'])->find();
			// var_dump($user_info['passwd']);die;
			//用户存在且可用
			if($user_info&&$user_info['status']==1){
                //记住密码：Levender2017-01-11
                if(isset($data['remember'])){
                    Cookie::set('admin',['username'=>$data['username'],'password'=>$data['password']],time()+3600*24*7);
                }else{
                    if(Cookie::has('admin')){
                        Cookie::delete('admin');
                    }
                }
                //记住密码：Levender2017-01-11
				//验证密码
				
				if(think_ucenter_encrypt($data['password'],config('PWD_KEY'))==$user_info['passwd']){
					
					$group=Db::name('auth_group_access')->where('uid',$user_info['admin_id'])->find();
					
			        $auth = array(
			            'uid'             => $user_info['admin_id'],
			            'username'        => $user_info['user_name'],
			            'group_id'			  => $group['group_id']			          
					 );			
					
				    User::store_logined_user($auth);
				
			        $data = array();
			        $data['admin_id']	=	$user_info['admin_id'];
			        $data['last_login_time']	=	time();				
			        $data['login_count']		=	array('exp','login_count+1');
					$data['last_login_ip']	=	get_client_ip();
					
			        Db::name('admin')->update($data);

					User::get_logined_user()->storage_user_action('登录了后台系统');
					
					return $this->success('登录成功！',url('Index/index'));
				}else{
					return  $this->error('密码错误！');
				}
			}else{
				return  $this->error('用户不存在或被禁用！');
			}				

        } else {
        	
            if(User::is_login()){
                $this->redirect('Index/index');
            }else{			
                return $this->fetch('public/login'); 
            }
        }
		
	}

	
}
