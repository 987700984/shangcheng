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

namespace osc\admin\controller;
use \think\Db;
use think\Request;
use osc\common\model\GoodsComment;
use osc\common\model\Goods;
use osc\common\model\Admin;
use osc\common\controller\AdminBase;
use \think\Session;
class GoodsBase extends AdminBase{


public function a($data,$die=0){
    echo "<pre>";
    var_dump($data);
    if($die==0){
        die;
    }
}

}
