<?php namespace Hardywen\Wxpay\JsApi;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Hardywen\Wxpay\lib\Common;
use Hardywen\Wxpay\lib\UnifiedOrder;
use Hardywen\Wxpay\lib\Notify;

class JsApi {

	use Common, UnifiedOrder, Notify;


	var $wxpay_config  = [
		'body' => '',
		'total_fee' => '',
		'out_trade_no' => '',
		'sub_mch_id' => '',
		'device_info' => '',
		'attach' => '',
		'time_start' => '',
		'time_expire' => '',
		'goods_tag' => '',
		'product_id' => '',
	];
	var $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
	var $code;//code码，用以获取openid
	var $openid;//用户的openid
	var $parameters;//jsApi参数，格式为json
	var $prepay_id;//使用统一支付接口得到的预支付id
	var $curl_timeout;//curl超时时间

	function __construct($config)
	{
		$this->wxpay_config = $this->wxpay_config = array_merge($this->wxpay_config,$config);

		//设置curl超时时间
		$this->curl_timeout = $this->wxpay_config['curl_timeout'];

	}

	function setConfig($config){

		$this->wxpay_config = array_merge($this->wxpay_config,$config);

		return $this;
	}


	function pay(){

		$jsApiParameters = $this->getOpenid()->jsApiParameters();
        $return_url = $this->wxpay_config['call_back_url'];
		return View::make('wxpay::pay',compact('jsApiParameters','return_url'))->render();
	}

	function verifyNotify(){

		$notify = $this->checkSign();

		if($this->wxpay_config['log']){
			$data = json_encode($this->getData());

			\Log::info("===============WeiXin pay notify result==============:$notify. Data:$data");
		}

		return $notify;

	}

	function jsApiParameters(){

		$this->setParameter("openid", $this->openid);//商品描述
		$this->setParameter("notify_url", $this->wxpay_config['notify_url']);//通知地址
		$this->setParameter("trade_type", "JSAPI");//交易类型

		//订单相关
		$this->setParameter("body", $this->wxpay_config['body']);//商品描述

		$this->setParameter("out_trade_no", $this->wxpay_config['out_trade_no']);//商户订单号
		$this->setParameter("total_fee", $this->wxpay_config['total_fee']);//总金额

		//非必填参数，商户可根据实际情况选填
		$this->setParameter("sub_mch_id",$this->wxpay_config['sub_mch_id']);//子商户号
		$this->setParameter("device_info",$this->wxpay_config['device_info']);//设备号
		$this->setParameter("attach",$this->wxpay_config['attach']);//附加数据
		$this->setParameter("time_start",$this->wxpay_config['time_start']);//交易起始时间
		$this->setParameter("time_expire",$this->wxpay_config['time_expire']);//交易结束时间
		$this->setParameter("goods_tag",$this->wxpay_config['goods_tag']);//商品标记
		$this->setParameter("product_id",$this->wxpay_config['product_id']);//商品ID

		$prepay_id = $this->getPrepayId();

		$this->setPrepayId($prepay_id);

		$jsApiParameters = $this->getParameters();
		//echo $jsApiParameters;exit;
		return $jsApiParameters;
	}
	
	
	/**
	 * 	作用：生成可以获得code的url
	 */
	function createOauthUrlForCode($redirectUrl)
	{
		if($redirectUrl === ''){
			$redirectUrl = \Request::url();
		}
		$urlObj["appid"] = $this->wxpay_config['appid'];
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}

	/**
	 * 	作用：生成可以获得openid的url
	 */
	function createOauthUrlForOpenid()
	{
		$urlObj["appid"] = $this->wxpay_config['appid'];
		$urlObj["secret"] = $this->wxpay_config['app_secret'];
		$urlObj["code"] = $this->code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}


	/**
	 * 	作用：通过curl向微信提交code，以获取openid
	 */
	function getOpenid()
	{
		if (!isset($_GET['code']))
		{
			//触发微信返回code码
			$url = $this->createOauthUrlForCode($this->wxpay_config['js_api_call_url']);
			Header("Location: $url");
		}else
		{
			//获取code码，以获取openid
			$code = $_GET['code'];
			$this->setCode($code);
		}

		$url = $this->createOauthUrlForOpenid();
		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//运行curl，结果以jason形式返回
		$res = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($res,true);

		if(array_key_exists('errcode',$data)){
			throw new \Exception($data['errcode'] . '-' . $data['errmsg']);
		}

		$this->openid = $data["openid"];

		return $this;
	}

	/**
	 * 	作用：设置prepay_id
	 */
	function setPrepayId($prepayId)
	{
		$this->prepay_id = $prepayId;
	}

	/**
	 * 	作用：设置code
	 */
	function setCode($code_)
	{
		$this->code = $code_;
	}

	/**
	 * 	作用：设置jsApi的参数
	 */
	public function getParameters()
	{
		$jsApiObj["appId"] = $this->wxpay_config['appid'];
		$timeStamp = time();
		$jsApiObj["timeStamp"] = "$timeStamp";
		$jsApiObj["nonceStr"] = $this->createNonceStr();
		$jsApiObj["package"] = "prepay_id=$this->prepay_id";
		$jsApiObj["signType"] = "MD5";

		$jsApiObj["paySign"] = $this->getSign($jsApiObj);

		$this->parameters = json_encode($jsApiObj);

		return $this->parameters;
	}



}
