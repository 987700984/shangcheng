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

use think\Controller;

class App extends Controller
{

    protected function _initialize()
    {



        $config = cache('db_config_data');

        if (!$config) {
            $config = load_config();
            cache('db_config_data', $config);
        }

        config($config);
        $this->getData();
    }


    function handle_img($arr, $image, $width = 100, $height = 100)
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k][$image] = config('SITE_URL') . resize($v[$image], $width, $height);
            } else {
                if ($k == $image) {
                    $arr[$image] = config('SITE_URL') . resize($v, $width, $height);

                }
            }
        }
        return $arr;
    }

    function hand_img($arr, $image)
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                if ($v[$image] != null) {
                    if(!preg_match('/http/',$v[$image],$matches)){
                        $arr[$k][$image] = config('SITE_URL') ."public/uploads/images/product/" . $v[$image];

                    }
                }

            } else {
                if ($k == $image) {
                    if ($v != null) {
                        if(!preg_match('/http/',$v,$matches)){
                            $arr[$image] = config('SITE_URL') ."public/uploads/images/product/" . $v;

                        }                    }
                }

            }

        }
        return $arr;
    }


    //判定是从哪里来的数据并接收
    public function getData(){


        $data = $this->dataInit();
        foreach($data as $key =>$v){
            $data[$key]=str_replace('"','',$v);
        }
        return $data;
    }

    //生成唯一订单号
    function build_order_no(){
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    public function  show($code,$msg='',$data=''){
        $a=['code'=>$code,'msg'=>$msg,'data'=>$data];
        $b=json_encode($a);

        echo $b;
    }


    //允许ＡＰＰ跨域访问
    protected function dataInit(){

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST");
        header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
        $data = file_get_contents('php://input');
        $data = urldecode($data);
        $data = $this->getAppPostData($data);

        return $data;
    }

    //    将APP异步的原生数据转换成数组形式
    protected function getAppPostData($data){
        $result = [];
        if(!$data){
            return $result;
        }
        $aa = explode('&',$data);
        if(empty($aa)){
            return $aa;
        }
        foreach($aa as $v){
            $temp = explode('=',$v);

            $result[$temp[0]] = $temp[1];
        }
        return $result;
    }

    //    获得随机字符串
    public  function createRandomStr($length,$str2=''){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        if($str!=''){
            $str = '0123456789';
        }
        $strlen = 62;
        while($length > $strlen){
            $str .= $str;
            $strlen += 62;
        }
        $str = str_shuffle($str);
        return substr($str,0,$length);
    }

}
