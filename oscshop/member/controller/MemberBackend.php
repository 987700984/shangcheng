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
use osc\admin\service\User;
use osc\common\model\Member;

class MemberBackend extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','会员');
		$this->assign('breadcrumb2','会员管理');
	}
	
     public function index(){     	

		$param=input('param.');
		
		$query=[];
		
		if(isset($param['user_name'])){		
			$map['m.username']=['like',"%".$param['user_name']."%"];
			$query['m.username']=urlencode($param['user_name']);
		}
		$map['m.uid']=['gt',0];
		

		$list=Db::name('member')->alias('m')->field('m.*,mag.title')	
		->join('member_auth_group mag','m.groupid = mag.id')
		->where($map)->order('m.uid desc')->paginate(config('page_num'),false,$query);
	
		if(0==$list->total()&&isset($param['user_name'])){			
			unset($map['username']);
			if(isset($param['user_name'])){			
				$map['m.nickname']=['like',"%".$param['user_name']."%"];
				$query['m.username']=urlencode($param['user_name']);		
				
				$list=Db::name('member')->alias('m')->field('m.*,mag.title')	
				->join('member_auth_group mag','m.groupid = mag.id')
				->where($map)->order('m.uid desc')->paginate(config('page_num'),false,$query);
			}	
		}
		
		$this->assign('list',$list);
				
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		
    	return $this->fetch();
	 }
	 public function add(){
	 	
		if(request()->isPost()){
			$date=input('post.');
			$result = $this->validate($date,'Member');			
			if($result!==true){
			
				return ['error'=>$result];
			}
			$member['username']=$date['username'];
			$member['password']=think_ucenter_encrypt($date['password'],config('PWD_KEY'));
			$member['regdate']=time();
			$member['reg_type']='pc';
			$member['email']=$date['email'];
			$member['telephone']=$date['telephone'];
			$member['groupid']=$date['groupid'];
			
			$uid=Db::name('member')->insert($member,false,true);
			
			if($uid){

				User::get_logined_user()->storage_user_action('新增了会员');
			
				return ['success'=>'新增成功','action'=>'add'];
			}else{
				return ['error'=>'新增失败'];
				
			}
			
		}
		$this->assign('group',Db::name('member_auth_group')->field('id,title')->select());
		$this->assign('crumbs','新增');
	 	return $this->fetch();
	 }
 	 public function edit(){
	 	
		if(request()->isPost()){
		
			$date=input('post.');			
//			$member['password']=think_ucenter_encrypt($date['password'],config('PWD_KEY'));
			$member['password'] = 1;
			$member['email']=$date['email'];
			$member['is_black']=isset($date['is_black'])&&$date['is_black']?1:0;
			$member['telephone']=$date['telephone'];
			$member['groupid']=$date['groupid'];
			if(Db::name('member')->where('uid',$date['uid'])->update($member)){

				User::get_logined_user()->storage_user_action('编辑了会员');
				$this->success('编辑成功',url('MemberBackend/index'));
			}else{
				$this->error('编辑失败');
			}
		}
		
		$list=[
			'info'=>Db::name('member')->find(input('param.id')),
			'address'=>Db::name('address')->where('uid',input('param.id'))->select()
		];

//		 艺拍项目看到用户等级
$member = Member::get(['uid'=>input('param.id')]);
		 $level = $member->user_level;
		 $this->assign('level',$level);


		$this->assign('data',$list);
		$this->assign('group',Db::name('member_auth_group')->field('id,title')->select());
		$this->assign('crumbs','会员资料');
	 	return $this->fetch('info');
	 }
 
}
