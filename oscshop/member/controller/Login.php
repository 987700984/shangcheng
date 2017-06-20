<?php
/**
 * @author    深圳韦恩斯科技有限公司
 *会员登录注册相关
 */
namespace osc\member\controller;

use osc\common\controller\Base;
use think\Db;
use think\captcha\Captcha;
use osc\member\service\User;

class Login extends Base
{


    //登录验证
    public function validate_login($data)
    {

        if (empty($data['telephone'])) {
            return ['error' => '手机号必填'];
        } elseif (empty($data['password'])) {
            return ['error' => '密码必填'];
        }

        if (1 == config('use_captcha')) {
            if (!check_verify($data['captcha'])) {
                return ['error' => '验证码错误'];
            }
        }

        $user = Db::name('member')->where('username', $data['telephone'])->find();

        if (!$user) {
            return ['error' => '账号不存在！！'];
        } elseif (($user['checked'] == 0) && (1 == config('reg_check'))) {//需要审核
            return ['error' => '该账号未审核通过！！'];
        }

        if (think_ucenter_encrypt($data['password'], config('PWD_KEY')) == $user['password']) {

            $auth = array(
                'uid' => $user['uid'],
                'nickname' => $user['nickname'],
                'group_id' => $user['groupid'],
            );

            User::store_logined_user($auth);

            $login['lastdate'] = time();
            $login['loginnum'] = array('exp', 'loginnum+1');
            $login['lastip'] = get_client_ip();

            Db::name('member')->where('uid', $user['uid'])->update($login);
            session('username', $user['username']);
            $logined_user = User::get_logined_user();
            $logined_user->storage_user_action('登录了网站');

            return ['success' => '登录成功', 'total' => $logined_user->carts()->count_cart_total()];
        } else {
            return ['error' => '密码错误'];
        }
    }

    //注册
    public function reg()
    {
        if (request()->isPost()) {


            $data = input('post.');

            if (!$data['code'] == cookie('code')) {

                return ['error' => '验证码错误'];
            }
            $result = $this->validate($data, 'Member');
            if (true !== $result) {
                return ['error' => $result];
            }

            $member['password'] = think_ucenter_encrypt($data['password'], config('PWD_KEY'));
            $member['username'] = $data['telephone'];
            $member['telephone'] = $data['telephone'];
            $member['groupid'] = config('default_group_id');
            $member['reg_type'] = 'pc';
            $member['regdate'] = time();
            $member['lastdate'] = time();
            if (isset($data['pid'])) {
                $pid = Db::name('member')->where('telephone', $data['pid'])->find();
                if ($pid) {
                    $member['pid'] = $pid['uid'];
                }
            }
            if (1 == config('reg_check')) {//需要审核或者验证
                $member['checked'] = 0;
            } else {
                $member['checked'] = 1;
            }

            $uid = Db::name('member')->insert($member, false, true);

            if ($uid) {

                cookie('code', null);
                $auth = array(
                    'uid' => $uid,
                    'nickname' => $member['nickname'],
                    'group_id' => $member['groupid'],

                );

                User::store_logined_user($auth);

                User::get_logined_user()->storage_user_action('注册成为会员');
                return ['success' => '注册成功'];

            }

        }

        if (User::is_login()) {
            return ['error' => '您已经登录了账号！！'];
        }


    }

//微信登录
    public function wei_login()
    {

        $code = input('param.code');

        if (isset($code)){
            $wechat =OscshopWechat::getInstance([
                'appid'=>config('appid'),
                'appsecret'=>config('appsecret'),
                'token'=>config('token'),
                'encodingaeskey'=>config('encodingaeskey')]);

            $openid = $wechat->native_login($code);
            if ($openid) {
                cookie('openid', $openid, 900);
                $this->redirect('Login/blind');
            } else {

                $this->redirect('Member/index');
            }
        }


    }

//微信绑定手机
    public function blind()
    {
        if (request()->isPost()) {

            $data = input('post.');

            if ($data['code'] != cookie('code')) {

                return ['error' => '验证码错误'];
            }
            if ($data['phone'] != cookie('phone')) {

                return ['error' => '手机号码不是发送验证码的手机号'];
            }
            $wechat = wechat();
            $status = $wechat->wechat_bind(cookie('openid'), $data);
            if ($status) {
                return ['success' => '绑定成功', 'url' => url('Member/index')];
            } else {
                return ['error' => '绑定失败'];
            }
        }


    }

    //获取地区
    function getarea()
    {
        $where['area_parent_id'] = input('param.areaId');

        return Db::name('area')->where($where)->select();
    }

    public function user_login()
    {
        return $this->fetch();
    }

    //登录
    public function login()
    {

        if (request()->isPost()) {

            $data = input('post.');

            $r = $this->validate_login($data);
            return $r;


        }
        if (User::is_login()) {
            return ['error' => '您已经登录了账号！！'];
        }


    }

    function logout()
    {
        //@todo 登出就清理购物车是没有必要的
        osc_order()->clear_cart(member('uid'));
        User::logout();

        $this->redirect('/');
    }

    public function verify()
    {
        $captcha = new Captcha((array)Config('captcha'));
        return $captcha->entry(1);
    }

    public function send()
    {

        import('phone/ChuanglanSmsApi', EXTEND_PATH);
        $clapi = new \ChuanglanSmsApi();
        $code = rand(456783, 789561);
        $phone = input('post.telephone');
        $content = '动态码' . $code . ',有效期为9分钟,请勿将动态码和密码告知他人!';
        if ($clapi->sendSMS($phone, $content)) {
            cookie('code', $code, 900);
            cookie('phone', $phone, 900);
            return true;

        } else {
            return false;
        }
    }


}
