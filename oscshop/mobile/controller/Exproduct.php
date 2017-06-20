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
namespace osc\mobile\controller;
use think\Db;
use think\Request;
use osc\common\model\Goods;
class Exproduct extends MobileBase
{
  
    public function index(){
        switch (input('param.id')) {
            case 0:
                $list=Db::name('goods')->where('pay_points',1)->order('goods_id desc')->paginate(10);
                break;
            case 1:
                $list=Db::name('goods')->where('pay_points','not in',1)->where('is_points_goods',1)->order('goods_id desc')->paginate(10);
                break;
            default:

                $list= Db::name('goods')->alias('a')->join('goods_to_category w','a.goods_id = w.goods_id')->where(['w.category_id'=>input('param.id'),'a.status'=>1,'is_points_goods'=>1])->order('a.goods_id desc')->paginate(10);
        }

        $this->assign('SEO',['title'=>'分类-'.config('SITE_TITLE')]);
        $this->assign('mun',count($list));
        $this->assign('empty','暂时没有该产品的信息');
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function details(){
       $comment=Db::name('goods_comment')->where(['goods_id'=>input('param.id'),'status'=>1])->order('id desc')->paginate(2);
        if(!$list=osc_goods()->get_goods_info((int)input('param.id'))){
            $this->error('商品不存在！！');
        }
        $good = Goods::get((int)input('param.id'));
        $good->updateViewed();
        $this->assign('SEO',['title'=>$list['goods']['name'].'-'.config('SITE_TITLE'),
        'keywords'=>$list['goods']['meta_keyword'],
        'description'=>$list['goods']['meta_description']]);

        $this->assign('qq','tencent://message/?uin='.config('qq').'&Site=admin5.com&Menu=yes');
        $this->assign('list',Db::query('SELECT av.value_name FROM '.config('database.prefix').'goods_attribute ga,'.config('database.prefix').'attribute_value av WHERE av.attribute_value_id=ga.attribute_value_id AND ga.goods_id='.(int)input('param.id')));
        $this->assign('empty','暂时还没有人评论');
        $this->assign('comment',$comment);
        $this->assign('goods',$list['goods']);
        $this->assign('image',$list['image']);
        $this->assign('options',$list['options']);
        $this->assign('discount',$list['discount']);
        $this->assign('collect',Db::name('collect')->where(['uid'=>member('uid'),'goods_id'=>$list['goods']['goods_id'],'is_points_goods'=>1])->find());
        $this->assign('mobile_description',$list['mobile_description']);
        // var_dump($list['image']);die;
        return $this->fetch();
    }

//添加评论
    public function addComent(){
        if (request()->isAjax()) {
            $data=input('post.');
            if (!member('uid')) {
                $data['user_name'] = '游客';
            }else{
                $data['user_name'] = member('nickname');
                $data['userpic']=member('userpic');
            }
            $data['status']    = 2;
            $data['add_time']  = date('Y-m-d,H-i-s');
            Db::name('goods_comment')->insert($data);
            
            return true;
        }
            
    }

//全部分类
    public function category(){
        $this->assign('category',Db::name('category')->where('pid',37)->select());

        $this->assign('cate',osc_goods()->get_category_goods(37));
        $this->assign('SEO',['title'=>'分类-'.config('SITE_TITLE')]);
        // var_dump(osc_goods()->get_category_goods(37));die;
        return $this->fetch();
    }

    public function ajaxIndex(){
        if (request()->isAjax()) {
            $list=Db::name('goods')->alias('a')->join('goods_to_category w','a.goods_id = w.goods_id')->where(['a.status'=>1])->where('a.goods_id','<',input('post.goods_id'))->order('a.goods_id desc')->limit(10)->select();
            if ($list) {
                return $list;
            }else{
                return false;
            }
        }   
    }
}
