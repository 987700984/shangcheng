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
 * 公共数据获取
 * 
 */
namespace osc\common\service;

class Api{

	public $params;
	public $app;
	/**
	 *处理APP的更新版本接口
	 */
	public function check($data){
		$this->params['app_id'] =$appId= isset($data['appId'])?$data['appId']:'';
		$this->params['version_id'] =$version_id= isset($data['version_id'])?$data['version_id']:'';
		$this->params['version_mini'] =$version_mini =isset($data['version_mini'])?$data['version_mini']:'';
		$this->params['did'] =$did= isset($data['did'])?$data['did']:'';
		$this->params['encrypt_did'] =$encrypt_did= isset($data['encrypt_did'])?$data['encrypt_did']:'';
		if (!is_numeric($appId) || !is_numeric($version_id)) {
			return $this->show('401','参数不合法');
		}
		//判断app是否要加密
		$this->app=$this->getApp($appId);
		if ($this->app) {
			return $this->show('402','app_id不存在');
		}
		if ($this->app['is_encryption'] && $encrypt_did!=md5($did.$this->app['key'])) {
			return $this->show('403','没有该权限');
		}
		
	}

	public function getApp($id){
		return Db::name('app')->where(['id'=>$id,'status'=>1])->find();
	}

	/**
	 * 按综合方式输出通信数据
	 * @param integer $code 状态码
	 * @param integer $message 提示信息
	 * @param integer $data 数据
	 * @param integer $type 返回数据类型
	 * return string 
	 * */
	public function show($code,$message='',$data=array()){
		$type=isset(input('param.format'))?input('param.format'):'json';
		if ($type=='json') {
			$this->json($code,$message,$data);
			exit;
		}
		if ($type=='xml') {
			$this->xml($code,$message,$data);
			exit;
		}
		$result = [
					'code'    => $code,
					'message' => $message,
					'data'    => $data
				];
		if ($type=='array') {
			return $result;
		}
	}

	/**
	 * 按json方式输出通信数据
	 * @param integer $code 状态码
	 * @param integer $message 提示信息
	 * @param integer $data 数据
	 * return string 
	 * */
	public  function json($code,$message='',$data=array()){
		if (!is_numeric($code)) {
		   return '';	
		}
		$result = [
					'code'    => $code,
					'message' => $message,
					'data'    => $data
				];
		echo json_encode($data);
		exit;
	}

	/**
	 * 按Xml方式输出通信数据
	 * @param integer $code 状态码
	 * @param integer $message 提示信息
	 * @param integer $data 数据
	 * return string 
	 * */
	public  function xml($code,$message='',$data=array()){
		if (!is_numeric($code)) {
		   return '';	
		}
		$result = [
					'code'    => $code,
					'message' => $message,
					'data'    => $data
				];
		header("Conent-Type:txt/xml");
		$xml= "<?xml version='1.0'endcoding='UTF-8'?>";
		$xml.=$this->toXml($result);
		$xml.="</root>";
		echo $xml;
		exit;
	}

	public function toXml($data){
		$xml=$attr="";
		foreach ($data as $key => $val) {
			if (is_numeric($key)) {
				$attr=" id='{$key}'";
				$key="item";
			}
			$xml.='<'.$key.$attr.'>';
			$xml.=is_array($val)?$this->toXml($val):$val;
			$xml.='</'.$key.'>';
		}
		return $xml;
	}



}