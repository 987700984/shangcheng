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

use osc\common\controller\Base;
use osc\common\model\JournalAccount;
use \think\Db;
use wechat\Curl;
use osc\common\model\Test;
use \wechat\OscshopWechat;
use osc\member\service\User;
use \think\Request;
use osc\common\model\Member;
use osc\common\model\Auctioning;
use think\session;

class Payment extends APP
{

    function  charge_config(){
        return config('charge_config');
    }

    //支付宝，充值-支付
    function alipay_charge()
    {
            $config = payment_config('alipay');
            $alipay_config = array(
                "service" => 'mobile.securitypay.pay',
                "partner" => $config['partner'],
                "seller_id" => $config['partner'],
                "key" => $config['key'],
                "payment_type" => 1,
                "notify_url" => '',
                'return_url'=>'',
                "_input_charset" => trim(strtolower(strtolower('utf-8'))),
                "out_trade_no" => build_order_no(),
                "subject" => '点对点商城APP支付宝充值兑换券',
//                "total_fee" =>input('total') ,
                "total_fee" =>0.01 ,
                "show_url" => '',
                'transport' => 'http',
                'sign_type' => strtoupper('MD5'),
//                "app_pay" => "Y",//启用此参数能唤起钱包APP支付宝
                "body" => '',
            );


            $alipay = new \payment\alipay\Alipay($alipay_config, 'mobile');


            $url=$alipay->get_payurl();
            $ur=parse_url($url);
            echo $ur['query'];

    }


    //支付宝，我的订单-支付
    function alipay_repay()
    {

        $order_id = (int)input('order_id');
//        $check = osc_order()->check_goods_quantity($order_id);
//        if (isset($check['error'])) {
//            echo $check;die;
//        }

        $order = Db::name('order')->where('order_id', $order_id)->find();

        if ($order && ($order['order_status_id'] != config('paid_order_status_id'))) {

            $config = payment_config('alipay');

            $alipay_config = array(
                "service" => 'mobile.securitypay.pay',
                "partner" => $config['partner'],
                "seller_id" => $config['partner'],
                "key" => $config['key'],
                "payment_type" => 1,
                "notify_url" => '',
                'return_url'=>'',
                "_input_charset" => trim(strtolower(strtolower('utf-8'))),
                "out_trade_no" => $order['order_num_alias'],
                "subject" => '点对点商城APP支付宝支付',
                "total_fee" => 0.01,
                "show_url" => '',
                'transport' => 'http',
                'sign_type' => strtoupper('MD5'),
//                "app_pay" => "Y",//启用此参数能唤起钱包APP支付宝
                "body" => '',
            );


            $alipay = new \payment\alipay\Alipay($alipay_config, 'mobile');


            $url=$alipay->get_payurl();
            $ur=parse_url($url);
            echo $ur['query'];
        }
    }





    //微信，我的订单-》立即支付
    public
    function weixin_repay()
    {
        $order_id = (int)input('param.order_id');

        $check = osc_order()->check_goods_quantity($order_id);

        if (isset($check['error'])) {
            return ['code' => 404, 'msg' => $check['error']];
        }

        $order = Db::name('order')->where('order_id', $order_id)->find();

        if ($order && ($order['order_status_id'] != config('paid_order_status_id'))) {

            $return['pay_total'] = $order['total'];
            $return['subject'] = $order['pay_subject'];
            $return['pay_order_no'] = $order['order_num_alias'];

            return $this->getBizPackage($return);
        } else {
            return ['msg' => '订单已经支付'];
        }
    }

    //微信jssdk回调
    public
    function jsskd_notify()
    {

        if (wechat()->checkPaySign()) {

            $sourceStr = file_get_contents('php://input');

            // 读取数据

            $postObj = simplexml_load_string($sourceStr, 'SimpleXMLElement', LIBXML_NOCDATA);

//此处将SimpleXMLElement对象转换成普通的json对象
            $postObj = json_decode(json_encode($postObj, true));


            if (!$postObj) {
                echo "<xml><return_code><![CDATA[FAIL]]></return_code></xml>";
            } else {

                $order = Db::name('order')->where('order_num_alias', $postObj->out_trade_no)->find();


                if ($order) {

                    if ($order['order_status_id'] != config('paid_order_status_id')) {

                        osc_order()->update_order($order['order_id']);
                        osc_order()->handle_point($order['order_id'], member('uid'));


                    }
                    echo "<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>";

                } else {

//					如果订单表没查不到$postObj->out_trade_no，则为用户充值操作，以下记录用户操作记录

                    $journal = JournalAccount::get(['handle_id' => $postObj->transaction_id]);

//					如果还没有数据，则入库，若已经存储过，则不操作
                    if (!$journal) {
                        $goods_id = explode('-', $postObj->out_trade_no)[1];
//						用户支付保证金加入流水表
                        $member = Member::get(['wechat_openid' => $postObj->openid]);


//							若支付保证金入库成功，刚加入资金流水表
                        $journal_account = new JournalAccount([
                            'amount' => $postObj->total_fee,
                            'user_id' => input('param.uid'),
                            'type' => 1,
                            'handle_id' => $postObj->transaction_id,
                            'goods_id' => $goods_id
                        ]);
                        $journal_account->save();
                        Db::name('member')->where('uid', input('param.uid'))->setInc('cash_points', input('param.number'));
                        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>";


                    } else {
                        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>";
                    }
                }
            }

        } else {

            echo "<xml><return_code><![CDATA[FAIL]]></return_code></xml>";

        }
        die;
    }

    //微信支付 package
    function getBizPackage($data)
    {


        $wx = wechat();
        // 订单总额
        $totalFee = ($data['pay_total']) * 100;
        // 随机字符串
        $nonceStr = $wx->generateNonceStr();

        $config = payment_config('weixin');


        // 时间戳
        $timeStamp = strval(time());

        $pack = array(
            'appid' => $config['appid'],
            'body' => $data['subject'],
            'mch_id' => $config['weixin_partner'],
            'nonce_str' => $nonceStr,
            'notify_url' => request()->domain() . url('payment/jsskd_notify', $data),
            'spbill_create_ip' => get_client_ip(),
            'openid' => $wx->getOpenId(),
            // 外部订单号
            'out_trade_no' => $data['pay_order_no'],
            'timeStamp' => $timeStamp,
            'total_fee' => $totalFee,
            'trade_type' => 'JSAPI'
        );

        $pack['sign'] = $wx->paySign($pack);


        $xml = $wx->toXML($pack);


        $ret = Curl::post('https://api.mch.weixin.qq.com/pay/unifiedorder', $xml);


        $postObj = json_decode(json_encode(simplexml_load_string($ret, 'SimpleXMLElement', LIBXML_NOCDATA)));


        if (empty($postObj->prepay_id) || $postObj->return_code == "FAIL") {

            return json(['ret_code' => 11, 'bizPackage' => '']);
        } else {

            $packJs = array(
                'appId' => $config['appid'],
                'timeStamp' => $timeStamp,
                'nonceStr' => $nonceStr,
                'package' => "prepay_id=" . $postObj->prepay_id,
                'signType' => 'MD5'
            );


            $JsSign = $wx->paySign($packJs);

            $p['timestamp'] = $timeStamp;
            $p['nonceStr'] = $nonceStr;
            $p['package'] = "prepay_id=" . $postObj->prepay_id;
            $p['signType'] = 'MD5';
            $p['paySign'] = $JsSign;


            return json(['ret_code' => 0, 'bizPackage' => $p]);
        }
    }

//兑换券充值
    public
    function recharge()
    {

        if (request()->isGet()) {
            $data = input('param.');
            $data['pay_total'] = input('pay_total');
            $data['subject'] = '用户充值兑换券';
            $data['pay_order_no'] = build_order_no() . '-' . $data['uid'];

            return $this->getBizPackage($data);
        }

    }

}