# wxpay
自用
WeiXin Payment 

##更新
1. 增加参数call_back_url ，同步回调地址。

###微信支付的配置要点：请留心注意本部分内容，因为这很可能是你遇到的大坑。
1.网页授权（设置错误会出现redirect_url参数错误的错误）
这个网页授权需要登录微信公众平台，点击左侧菜单“开发者中心”，在右侧“权限列表”中找到“网页账号”，点击最右侧的修改，把测试的网址写进去，不要加http。

2.支付授权目录（设置不对会无法发起js支付，因为没有权限，错误为：“getBrandWCPayRequest:fail_no permission to execute”
）
设置好授权目录即可。


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
    
    return 'success';
  }else{
    
    //业务逻辑
    
    
	return 'fail';
  }
  
```

