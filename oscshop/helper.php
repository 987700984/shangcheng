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
 * 
 */
use \osc\common\service\Goods;
use \osc\common\service\Transport;
use \osc\common\service\Order;
use \oscshop\Hashids;
use \oscshop\Weight;

if (!function_exists('osc_goods')) {
    /**
     * 商品相关数据助手函数
     */
    function osc_goods()
    {
        return Goods::getInstance();
    }
}

if (!function_exists('osc_cart')) {
    /**
     * osc购物车助手函数
	 * 
     */
    function osc_cart()
    {    	
        return new \oscshop\Cart();        
    }
}

if (!function_exists('osc_weight')) {
    /**
     * osc重量相关助手函数
	 * 
     */
    function osc_weight()
    {    	
        return Weight::getInstance();       
    }
}
if (!function_exists('osc_transport')) {
    /**
     * osc运费相关助手函数
	 * 
     */
    function osc_transport()
    {    	
        return Transport::getInstance();       
    }
}
if (!function_exists('osc_order')) {
    /**
     * osc订单相关助手函数
	 * 
     */
    function osc_order()
    {    	
        return new Order();       
    }
}
if (!function_exists('hashids')) {
    /**
     * 数字id加密
     */
    function hashids()
    {
    	return new Hashids(config('PWD_KEY'),10);
    }
}
