<?php

 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者id，以2088开头的16位纯数字
//TODO:暂时不知道如何改成PLUS_PATH
require_once(dirname(dirname(dirname(__FILE__)))."/data/plus/config.php");

if($config['alipaytype']=="1")
{
		$dir = "alipay";
}else{
		$dir = "alipaydual";
}

require_once(dirname(dirname(dirname(__FILE__)))."/data/api/".$dir."/alipay_data.php");

//合作身份者id，以2088开头的16位纯数字
$alipay_config['partner']		= $alipaydata['sy_alipayid'];

//安全检验码，以数字和字母组成的32位字符
//如果签名方式设置为"MD5"时，请设置该参数
$alipay_config['key']			= $alipaydata['sy_alipaycode'];

//商户的私钥（后缀是.pen）文件相对路径
//如果签名方式设置为"0001"时，请设置该参数
$alipay_config['private_key_path']	= 'key/rsa_private_key.pem';
unset($alipay_config['private_key_path']);

//支付宝公钥（后缀是.pen）文件相对路径
//如果签名方式设置为"0001"时，请设置该参数
$alipay_config['ali_public_key_path']= 'key/alipay_public_key.pem';
unset($alipay_config['ali_public_key_path']);

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


//签名方式 不需修改
$alipay_config['sign_type']    = 'MD5';

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= 'utf-8';

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = $config['sy_weburl']."/api/wapalipay/cacert.pem";

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
//$alipay_config['partner']		= '';

//安全检验码，以数字和字母组成的32位字符
//如果签名方式设置为“MD5”时，请设置该参数
//$alipay_config['key']			= '';

//商户的私钥（后缀是.pen）文件相对路径
//如果签名方式设置为“0001”时，请设置该参数
//$alipay_config['private_key_path']	= 'key/rsa_private_key.pem';

//支付宝公钥（后缀是.pen）文件相对路径
//如果签名方式设置为“0001”时，请设置该参数
//$alipay_config['ali_public_key_path']= 'key/alipay_public_key.pem';


//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


//签名方式 不需修改
//$alipay_config['sign_type']    = '0001';

//字符编码格式 目前支持 gbk 或 utf-8
//$alipay_config['input_charset']= 'utf-8';

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
//$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
//$alipay_config['transport']    = 'http';
?>