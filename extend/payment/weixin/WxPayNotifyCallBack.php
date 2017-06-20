<?php
namespace payment\weixin;
use payment\weixin\WxPayApi;
use payment\weixin\WxPayConfig;
use payment\weixin\WxPayException;
use payment\weixin\WxPayNotify;
use payment\weixin\WxPayOrderQuery;
use think\Db;

class WxPayNotifyCallBack extends WxPayNotify
{
	private $para;
	//查询订单
	public function Queryorder($transaction_id)
	{
	
		$input = new WxPayOrderQuery();
		
		$input->SetTransaction_id($transaction_id);
		
		$result = WxPayApi::orderQuery($input);		
		
		if(array_key_exists("return_code", $result) && $result["return_code"] == "SUCCESS"
			)
		{
			return $result["return_code"];
		}
		return false;
	}
	
	//重写回调处理方法，成功的时候返回true，失败返回false，处理商城订单
	public function NotifyProcess($data)
	{
		$notfiyOutput = array();
				
		if(!array_key_exists("transaction_id", $data)){
			$this->para['code']=0;
			$this->para['data']='';
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$this->para['code']=0;
			$this->para['data']='';
			$msg = "订单查询失败";
			return false;
		}
		$this->para['code'] = 1;
		$this->para['data'] = $data;
		return true;

	}
//自定义方法 检查微信端是否回调成功
	public function is_success(){
		return $this->para; //返回数据
	}
}

