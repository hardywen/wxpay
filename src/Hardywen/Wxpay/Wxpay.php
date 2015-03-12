<?php namespace Hardywen\Wxpay;

use Hardywen\Wxpay\JsApi\JsApi;

class Wxpay {

	public $config;

	function __construct($config){
		$this->config = $config;
	}

	public function instance($type){

		switch($type){
			case 'jsApi':
				return new JsApi($this->config);
				break;
			default:
				return false;
				break;
		}
	}

}