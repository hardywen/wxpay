<?php namespace Hardywen\Wxpay\Facades;

use Illuminate\Support\Facades\Facade;

class WxpayFacade extends Facade {

	protected static function getFacadeAccessor() {
		return 'wxpay';
	}
}