<?php

use Util\AliAppPay\AopClient;

$aop = new AopClient;
$aop->alipayrsaPublicKey = '请填写支付宝公钥，一行字符串';
$flag = $aop->rsaCheckV1($_POST, NULL, "RSA");
