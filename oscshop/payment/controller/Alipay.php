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

namespace osc\payment\controller;
use osc\common\model\JournalAccount;
use osc\common\controller\Base;
use think\Db;

class Alipay extends Base
{

    //下单处理
    public function process()
    {

        return ['url' => $this->alipay_url(osc_order()->add_order())];
    }

    public function alipay_url($order, $type = '')
    {

        if ($order['order_id']) {

            $payment = payment_config('alipay');

            $payment['notify_url'] =request()->domain() . url('payment/alipay/alipay_return');

            $payment['return_url'] = request()->domain() . url('payment/alipay/alipay_return');//同步通知
            $payment['order_type'] = 'goods_buy';
            $payment['subject'] = '点对点商城订单支付';
            $payment['name'] = $order['name'];
            $payment['pay_order_no'] = $order['pay_order_no'];
            $payment['total_fee'] = $order['pay_total'];
            $alipay = new \payment\alipay\Alipay($payment);
            $url = $alipay->get_payurl();

            if ($type == 're_pay') {
                session('re_pay_order_id', null);
            }

            return $url;
        }


    }

    public function re_pay($order_id)
    {

        $order = Db::name('order')->where('order_id', (int)$order_id)->find();

        if ($order && ($order['order_status_id'] != config('paid_order_status_id'))) {
            $url = $this->alipay_url([
                'order_id' => $order['order_id'],
                'subject' => $order['pay_subject'],
                'name' => $order['name'],
                'pay_order_no' => $order['order_num_alias'],
                'pay_total' => $order['total'],
                'uid' => $order['uid'],
            ], 're_pay'
            );
        }
        return ['type' => 'alipay', 'pay_url' => $url];
    }

//充值
    function charge_pay($data)
    {

        $url = $this->pay_url([

            'subject' => '点对点商城兑换券支付宝充值',
            'pay_order_no' => build_order_no() . member('uid'),
            'pay_total' => $data['total'],
            'uid' => member('uid'),
            'number'=>$data['number']
        ], 're_pay'
        );


        return ['type' => 'alipay', 'pay_url' => $url];
    }

    //充值
    public function pay_url($order,$type = '')
    {


        $payment = payment_config('alipay');
        cookie('data',$order);
        $payment['notify_url'] = request()->domain() . url('payment/alipay/pay_notify');

        $payment['return_url'] = request()->domain() . url('payment/alipay/pay_return');//同步通知
        $payment['order_type'] = 'goods_buy';
        $payment['subject'] = $order['subject'];
        $payment['pay_order_no'] = $order['pay_order_no'];
        $payment['total_fee'] = $order['pay_total'];
        $payment['name'] = member('nickname');
        $alipay = new \payment\alipay\Alipay($payment);

        $url = $alipay->get_payurl();
        if ($type == 're_pay') {
            session('data', null);
        }

        return $url;


    }





    //充值同步通知
    public
    function pay_return()
    {

        $alipay = new \payment\alipay\Alipay(payment_config('alipay'));
        //对进入的参数进行远程数据判断
        $verify = $alipay->return_verify();

        if ($verify) {
            $get = input('param.');
            $data = cookie('data');
            $journal = JournalAccount::get(['handle_id' => $data['pay_order_no']]);
            if (!$journal) {

//						用户支付保证金加入流水表


//							若支付保证金入库成功，刚加入资金流水表
                $journal_account = new JournalAccount([
                    'amount' => $data['pay_total'],
                    'user_id' => member('uid'),
                    'type' => 1,
                    'handle_id' => $data['pay_order_no'],

                ]);
                $journal_account->save();
                Db::name('member')->where('uid', member('uid'))->setInc('cash_points', $data['number']);
                if ($get['trade_status'] == 'TRADE_SUCCESS') {

                    $this->redirect(url('index/Member/index'));
                }
            }else {
                die('支付错误');
            }

        } else {
            die('支付失败');
        }

    }

    //同步通知
    public
    function alipay_return()
    {

        $alipay = new \payment\alipay\Alipay(payment_config('alipay'));
        //对进入的参数进行远程数据判断
        $verify = $alipay->return_verify();

        if ($verify) {

            $get = input('param.');

            $order = Db::name('order')->where('order_num_alias', $get['out_trade_no'])->find();

            if ($order['order_status_id'] == config('paid_order_status_id')) {
                $this->redirect( url('index/PaySuccess/pay_success', 'order_id=' . $order['order_id']));
                die;
            }

            if ($order && ($order['order_status_id'] != config('paid_order_status_id'))) {
                //支付完成
                if ($get['trade_status'] == 'TRADE_SUCCESS') {

                    osc_order()->update_order($order['order_id']);
                    osc_order()->handle_point($order['order_id'], $order['uid']);

                    $this->redirect( url('index/PaySuccess/pay_success', 'order_id=' . $order['order_id']));
                }
            } else {
                die('订单不存在');
            }

        } else {
            die('支付失败');
        }

    }

}
