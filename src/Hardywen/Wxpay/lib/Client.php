<?php namespace Hardywen\Wxpay\lib;


Trait Client{

	public $response;//微信返回的响应
	public $result;//返回参数，类型为关联数组

	/**
	 * 	作用：设置请求参数
	 */
	function setParameter($parameter, $parameterValue)
	{
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}

//	/**
//	 * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
//	 */
//	function createXml()
//	{
//		$this->parameters["appid"] = $this->wxpay_config['appid'];//公众账号ID
//		$this->parameters["mch_id"] = $this->wxpay_config['mchid'];//商户号
//		$this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
//		$this->parameters["sign"] = $this->getSign($this->parameters);//签名
//		return  $this->arrayToXml($this->parameters);
//	}

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
}