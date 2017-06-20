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

class Payment extends Base
{   
 
    //数据验证
    private function validate_pay($type = 'money')
    {
        $user = User::get_logined_user();

        if (!$user) {
            return ['error' => '请先登录'];
        }

        $cart = osc_cart();

        if (!$user->carts($type)->count_cart_total()) {
            return ['error' => '您的购物车没有商品'];
        }

        $city_id = input('post.city_id');

        $shipping = $user->carts($type)->need_shipping();


        //需要配送的
        if ($shipping) {
            if ($city_id == '') {
                return ['error' => '请选择收货地址'];
            }
        }

        // 验证商品数量
        $cart_list = Db::name('cart')->where('uid', $user->uid)->select();

        foreach ($cart_list as $k => $v) {

            $param['option'] = json_decode($v['goods_option'], true);
            $param['goods_id'] = $v['goods_id'];
            $param['quantity'] = $v['quantity'];

            if ($error = $cart->check($param)) {
                return $error;
            }


        }
        return [
            'uid' => $user->uid,
            'address_id' => $city_id,
            'shipping' => $shipping
        ];
    }


    //微信支付
//    function weixin_pay()
//    {
//        if (request()->isGet()) {
//
//            $jssdk_order = session('jssdk_order');
//
//            if (!empty($jssdk_order)) {
//
//                $return = $jssdk_order;
//
//            } else {
//                    session('jssdk_order', input('param.'));
//                return $this->getBizPackage($return);
//
//            }
//        }
//    }

        //微信，我的订单-》立即支付
        public function weixin_repay()
        {
            $order_id = (int)input('param.order_id');

            $check = osc_order()->check_goods_quantity($order_id);

            if (isset($check['error'])) {
                return $check;
            }

            $order = Db::name('order')->where('order_id', $order_id)->find();

            if ($order && ($order['order_status_id'] != config('paid_order_status_id'))) {

                $return['pay_total'] = $order['total'];
                $return['subject'] = $order['pay_subject'];
                $return['pay_order_no'] = $order['order_num_alias'];

                return $this->getBizPackage($return);
            } else {
                return ['error' => '订单已经支付'];
            }
        }

        //微信jssdk回调
        public function jsskd_notify()
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
                            osc_order()->handle_point($order['order_id'],member('uid'));



                        }
                        echo "<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>";

                    } else {

//					如果订单表没查不到$postObj->out_trade_no，则为用户充值操作，以下记录用户操作记录

                        $journal = JournalAccount::get(['handle_id' => $postObj->transaction_id]);

//					如果还没有数据，则入库，若已经存储过，则不操作
                    if (!$journal) {
                       $goods_id = explode('-',$postObj->out_trade_no)[1];
//						用户支付保证金加入流水表
                        $member = Member::get(['wechat_openid' => $postObj->openid]);


//							若支付保证金入库成功，刚加入资金流水表
                            $journal_account = new JournalAccount([
                                'amount' => $postObj->total_fee,
                                'user_id' => member('uid'),
                                'type' => 1,
                                'handle_id' => $postObj->transaction_id,
                                'goods_id' => $goods_id
                            ]);
                            $journal_account->save();
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
                'notify_url' => request()->domain() . url('payment/jsskd_notify'),
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
    public function recharge(){
        $user = User::get_logined_user();
        if (request()->isPost()) {
            $data = [];
            $data['pay_total'] = input('pay_total');
            $data['subject'] = '用户充值兑换券';
            $data['uid'] = $user->uid;
            $data['pay_order_no'] = build_order_no().'-'.$data['uid'];
            Db::name('member')->where('uid',$data['uid'])->setInc('cash_points',input('param.number'));
            $user->storage_user_action('进行了充值兑换券操作');
            return $this->getBizPackage($data);
        }
        if (in_wechat()) {
            $wechat = wechat();
            $this->assign('signPackage', $wechat->getJsSign(request()->url(true)));
        }
        $this->assign('SEO',['title'=>'购买兑换券-'.config('SITE_TITLE')]);
        return $this->fetch();
    }





}
