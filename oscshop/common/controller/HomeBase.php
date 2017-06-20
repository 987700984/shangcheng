<?php
/**
 *
 * @author    深圳韦恩斯科技有限公司
 *会员中心
 */
namespace osc\common\controller;
use osc\common\model\ShopCart;
use think\Db;
use osc\member\service\User;
class HomeBase extends Base{	
	
	protected function _initialize() {
		parent::_initialize();		
		
		$pid =Db::name('member')->where('uid',member('pid'))->find();
		if($pid){
			$this->assign('pid',$pid['nickname']);
		}else{
			$this->assign('pid','您没有推荐人');
		}

		$this->assign('appid',config('appid'));
		$this->assign('states',0);
		$this->assign('message',Db::name('message')->select());
		$this->assign('cart',Db::name('cart')->where('uid',member('uid'))->count());
		$this->assign('logo',Db::name('ads_items')->where('ad_id',3)->find());
		$this->assign('code',Db::name('ads_items')->where('ad_id',5)->find());
		$this->assign('mess_cate',Db::name('message_category')->select());
		$this->assign('copy',Db::name('config')->where('id',112)->find());
		$this->assign('cate1',Db::name('category')->where('pid',32)->select());
		$this->assign('cate2',Db::name('category')->where('pid',37)->select());
		$this->assign('number',['1'=>$this->num(1),'3'=>$this->num(3),'4'=>$this->num(4)]);
		$this->assign('member',User::get_logined_user());
//		dump(Db::name('config')->where('id',112)->find());die;
//		$this->assign('top_nav',osc_goods()->get_goods_category());
		//$this->assign('top_nav',osc_goods()->get_category_tree());
		
	}
	//统计订单数量
	public function num($id){
		$count = Db::name('order')->where('order_status_id',$id)->count();
		return $count;
	}
	public function sell($num){
		$list= Db::name('goods')->where(['is_points_goods'=>0,'status'=>1])->order("viewed desc")->limit($num)->select();
		// dump($list);die;
		foreach ($list as $key => $v) {
			if ($v['end_time']!==NULL) {

				if ( strtotime($v['end_time'])<time() ) {
					$list[$key]['end_time']=1;

				}else{
					$list[$key]['end_time']=strtotime($v['end_time'])*1000;

				}
			}
		}
		// var_dump($list[3]);die;
		return $list;
	}

}
