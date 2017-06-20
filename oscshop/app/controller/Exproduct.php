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
use osc\common\model\Goods;
class Exproduct extends APP
{
  
    public function index(){
        if(request()->isPost()){
            $id=input('post.id');
            switch ($id) {
                case 0:
                    $list=Db::name('goods')->where('pay_points',1)->where('is_points_goods',1)->order('goods_id desc')->select();
                    break;
                case 1:
                    $list=Db::name('goods')->where('pay_points','not in',1)->where('is_points_goods',1)->order('goods_id desc')->select();
                    break;
                default:

                    $list= Db::name('goods')->alias('a')->join('goods_to_category w','a.goods_id = w.goods_id')->where(['w.category_id'=>input('param.id'),'a.status'=>1,'is_points_goods'=>1])->order('a.goods_id desc')->select();
            }
            $lis=[];
            foreach ($list as $k=>$v){
                $lis[$k]=$v;
                $lis[$k]['image']=config('SITE_URL').resize($v['image'],100,100);
            }
//            $lis=$this->handle_img($lis,'image',100,100);


            return ['code'=>400 ,'msg'=>'请求数据成功','data'=>$lis];

        }
    }

    public function details(){
        if(request()->isPost()) {
            if (!$list = osc_goods()->get_goods_info((int)input('post.id'))) {
                return ['code'=>404 ,'msg'=>'产品不存在','data'=>''];
            }
            $comment = Db::name('goods_comment')->where(['goods_id' => input('post.id'), 'status' => 1])->order('id desc')->paginate(2);
            $good = Goods::get((int)input('post.id'));
            $good->updateViewed();
            $comment=$this->hand_img($comment,'userpic');
            $image=$this->handle_img($list['image'],'image',100,100);
            $goods=$this->handle_img($list['goods'],'image',100,100);
            $qq='tencent://message/?uin='.config('qq').'&Site=admin5.com&Menu=yes';
            $collect=Db::name('collect')->where(['uid' => input('post.uid'), 'goods_id' => $list['goods']['goods_id'], 'is_points_goods' => 1])->find();

            return ['code'=>400 ,'msg'=>'获取数据成功','data'=>['comment'=>$comment,'image'=>$image,'collect'=>$collect,'goods'=>$goods,'qq'=>$qq]];
        }
    }

//添加评论
    public function addComent(){
        if (request()->isPost()) {
            $data=$this->getData();
            if (!isset($data['uid'])) {
                $dat['user_name'] = '游客';
            }else{
                $member=Db::name('member')->where('uid',$data['uid'])->find();
                $dat['user_name'] = $member['nickname'];
                $dat['userpic']=$member['userpic'];
            }
            $dat['goods_id']=$data['goods_id'];
            $dat['content']=$data['content'];
            $dat['status']    = 2;
            $dat['add_time']  = date('Y-m-d,H-i-s');
            Db::name('goods_comment')->insert($dat);

            return ['code'=>400 ,'msg'=>'评论成功'];
        }
            
    }

//全部分类
    public function category(){

        if(request()->isPost()){

            $cate=Db::name('category')->where('pid',37)->select();
            $list=$this->handle_img(osc_goods()->get_category_goods(37),'image',100,100);
            $data=['list'=>$list,'banner'=>$cate];
            return ['code'=>400 ,'msg'=>'请求数据成功','data'=>$data];

        }
    }


}
