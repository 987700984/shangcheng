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
 
namespace osc\index\controller;
use osc\common\controller\HomeBase;
use osc\common\model\Order as OrderModel;
class PaySuccess extends HomeBase
{
    public function pay_success()
    {
        if(input('param.order_id')){
            $order_id =input('param.order_id');
            $order    = OrderModel::get($order_id);
            $this->assign('order',$order);
            $this->assign('SEO',['title'=>'支付成功-'.config('SITE_URL').config('SITE_TITLE')]);
            return $this->fetch();
        }else{
            $this->error('非法操作');
        }

   
    }
}
