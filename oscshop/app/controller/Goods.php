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
namespace osc\app\controller;
use think\Db;
use think\Session;
use osc\common\model\Goods as GoodsModel;
use osc\member\service\User;
use osc\common\model\GoodsComment;
use osc\common\model\AdsItems;
class Goods extends APP{
	
	
	//入会商品详情
    public function index()
    {    
    	
		if(!$list=osc_goods()->get_goods_info((int)input('param.id'))){
			$this->show(404,'产品不存在');
		}

		 if ($list['goods']['end_time']!==NULL) {
            if ( strtotime($list['goods']['end_time'])<time() ) {
              $list['goods']['end_time']=1;
            }else{
               $list['goods']['end_time']=strtotime($list['goods']['end_time'])*1000; 
            }
          }
        $good = GoodsModel::get((int)input('param.id'));
		$good->updateViewed();
		$comment = Db::name('goods_comment')->where(['goods_id' => input('post.id'), 'status' => 1])->order('id desc')->select();
		$commen=$this->hand_img($comment,'userpic');
		$image=$this->handle_img($list['image'],'image',100,100);
		$goods=$this->handle_img($list['goods'],'image',100,100);
		$qq='tencent://message/?uin='.config('qq').'&Site=admin5.com&Menu=yes';
		$collect=Db::name('collect')->where(['uid' => input('post.uid'), 'goods_id' => $list['goods']['goods_id'], 'is_points_goods' => 0])->find();
		$data=['comment'=>$commen,'image'=>$image,'collect'=>$collect,'goods'=>$goods,'qq'=>$qq];
		$this->show(400,'请求数据成功',$data);

    }

	public function ajaxIndex(){
    	if (request()->isAjax()) {
    		$list=Db::name('goods_comment')->where(['goods_id'=>input('post.goods_id'),'status'=>1])->where('id','<',input('post.id'))->order('id desc')->select();

    		if ($list) {
				$lis=$this->hand_img($list,'userpic');
				return ['code'=>400,'msg'=>'获取数据成功','data'=>$lis];
    		}else{
				return ['code'=>404,'msg'=>'获取数据失败','data'=>''];
    		}
    	}	
    }

}
