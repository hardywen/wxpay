# wxpay
自用
WeiXin Payment 


###Install

1. 修改composer.json文件,加入```"hardywen/wxpay": "dev-master"```
```json
  "require": {
    "hardywen/wxpay": "dev-master"
  }
```

2. 修改app/config/app.php
```php
'providers' => array(
  		'Hardywen\Wxpay\WxpayServiceProvider'
)


'aliases' => array(
		'Wxpay'           => 'Hardywen\Wxpay\Facades\WxpayFacade'
)
```

3. 运行```composer update ```命令
4. 运行```php artisan config:publish hardywen/wxpay```
5. 如有必要修改支付页面，运行```php artisan view:publish hardywen/wxpay```


###Usage

支付调用 
```php  
  $config = array(
    'body'=>'',
    'total_fee' =>'',
    ...
  );
  Wxpay::instance('jsApi')->setConfig($config)->pay();
```

支付回调

```php
  $wxpay = Wxpay::instance('jsApi');
  $notify = $wxpay->verifyNotify(); //验证回调
  
  if($notify){
    //业务逻辑
    
    $wxpay->setReturnParameter("return_code","SUCCESS");//设置返回码
    $wxpay->returnXml();
  }else{
    //业务逻辑
    $wxpay->setReturnParameter("return_code","FAIL");//返回状态码
		$wxpay->setReturnParameter("return_msg","签名失败");//返回信息
		$wxpay->returnXml();
  }
  
```

