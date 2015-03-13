<?php namespace Hardywen\Wxpay\lib;

use Illuminate\Exception;

Trait UnifiedOrder {


	public $response;//微信返回的响应
	public $result;//返回参数，类型为关联数组


	/**
	 * 生成接口参数xml
	 */
	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["out_trade_no"] == null)
			{
				throw new \Exception("缺少统一支付接口必填参数out_trade_no！"."<br>");
			}elseif($this->parameters["body"] == null){
				throw new \Exception("缺少统一支付接口必填参数body！"."<br>");
			}elseif ($this->parameters["total_fee"] == null ) {
				throw new \Exception("缺少统一支付接口必填参数total_fee！"."<br>");
			}elseif ($this->parameters["notify_url"] == null) {
				throw new \Exception("缺少统一支付接口必填参数notify_url！"."<br>");
			}elseif ($this->parameters["trade_type"] == null) {
				throw new \Exception("缺少统一支付接口必填参数trade_type！"."<br>");
			}elseif ($this->parameters["trade_type"] == "JSAPI" &&
				$this->parameters["openid"] == NULL){
				throw new \Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
			}
			$this->parameters["appid"] = $this->wxpay_config['appid'];//公众账号ID
			$this->parameters["mch_id"] = $this->wxpay_config['mchid'];//商户号
			$this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
			$this->parameters["nonce_str"] = $this->createNonceStr();//随机字符串
			$this->parameters["sign"] = $this->getSign($this->parameters);//签名
			return  $this->arrayToXml($this->parameters);
		}catch (Exception $e)
		{
			dd($e);
		}
	}


	/**
	 * 	作用：设置请求参数
	 */
	function setParameter($parameter, $parameterValue)
	{
		if($parameterValue !== '')
			$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}


	/**
	 * 	作用：post请求xml
	 */
	function postXml()
	{
		$xml = $this->createXml();
		$this->response = $this->postXmlCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}

	/**
	 * 	作用：使用证书post请求xml
	 */
	function postXmlSSL()
	{
		$xml = $this->createXml();
		$this->response = $this->postXmlSSLCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}

	/**
	 * 	作用：获取结果，默认不使用证书
	 */
	function getResult()
	{
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}

	/**
	 * 获取prepay_id
	 */
	function getPrepayId()
	{
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		$prepay_id = $this->result["prepay_id"];
		return $prepay_id;
	}
	
}