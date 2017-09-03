<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\lib\Alipay;


class Server {


    protected  $configs;

    public function __construct( array $configs)
    {
        $this->config = $configs;

    }

    public function getPrePayOrder($bizcontent) {
        $aop = new AopClient;
        $aop->gatewayUrl = $this->config['gatewayUrl'];
        $aop->appId = $this->config['appId'];

        //私钥文件路径（私钥路径和私钥值选其一）
        $aop->rsaPrivateKeyFilePath = $this->config['rsaPrivateKeyFilePath'];
        //私钥值（私钥路径和私钥值选其一）
        // $aop->rsaPrivateKey = $this->config['rsaPrivateKey'];

        $aop->format = $this->config['format'];
        $aop->charset = $this->config['charset'];
        $aop->signType = $this->config['signType'];

        //支付宝公钥文件路径（公钥路径和公钥值选其一）
        $aop->alipayPublicKey = $this->config['alipayPublicKey'];
        //支付宝公钥值（公钥路径和公钥值选其一）
        // $aop->alipayrsaPublicKey = $this->config['alipayrsaPublicKey'];

        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $request->setNotifyUrl($this->config['notifyUrl']);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response; 
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
//        return htmlspecialchars($response); //就是orderString 可以直接给客户端请求，无需再做处理。
    }
    
    /*
     * 支付异步服务器端处理逻辑
     */
    
    public function notify($post) {
        $aop = new AopClient;
        $aop->alipayrsaPublicKey = $this->config['alipayrsaPublicKey'];
        $flag = $aop->rsaCheckV1($post, NULL, "RSA2");
        if($flag) {
            return $post;
        } else {
            return $post;
//            return false;
        }
    }
    

    public function __autoload($classname) {
        // print_r("expression");die;
        $filename = __DIR__.'/'. $classname .".php";
        include_once($filename);
    }

}
