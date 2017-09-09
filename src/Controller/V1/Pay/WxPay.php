<?php

namespace Api\Controller\V1\Pay;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;
use Api\lib\WxPay\WxServer;

class WxPay extends Controller
{
    
    public function wxPay(Request $request, Response $response) 
    {
        $post = $this->getOauthRequest();
        if ($post['total'] && $post['type'] && $post['product_id']) {
            $user_id = $this->getTokenUserId($post['access_token'], $post['client_id']);
            if($user_id < 1 ){
                return $this->jsonError($response,41001,'前先登录！');
            }
            $type = intval($post['type']);
            $isPaid = $this->app->db->lq_orders()->where(['uid'=>$user_id,'product_type'=>$type,'product_id'=>$post['product_id'],'status'=>1])->fetch();
            if($isPaid){
                return $this->jsonError($response,42014,'您已购买过');
            }
            $beforePayCheckRes = $this->_createPayOrder($user_id, 1, $type, $post['product_id'],$post['total']);
            if($beforePayCheckRes){
                $bizcontent = $beforePayCheckRes;
            }else{
                return $this->jsonError($response,-1,'系统繁忙，请稍后再试！');
            }   
            $server = new WxServer();
            $return = $server->getPrePayOrder($bizcontent); 
            $data = ['order'=>$return];
            return $this->jsonSuccess($response, $data);
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
    }

}
