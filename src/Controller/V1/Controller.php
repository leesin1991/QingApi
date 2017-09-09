<?php

namespace Api\Controller\V1;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\lib\Sms;
use Api\lib\Alipay\Server;
use Api\lib\Wxpay\WxServer;

class Controller extends AbstractController
{

    public function sendSmsVcode($mobile, $type = 'register') {
        $mkey = $type . $mobile;
        $item = $this->redis->get($mkey);
        if (!$item) {
            $code = mt_rand(100000, 999999);
            $this->redis->setex($mkey, 60 * 30, $code);
            $smsConfig = $this->container->get('configs')['sms'];
            $sms = new Sms($smsConfig);
            $msg = $smsConfig['sign'].'注册验证码： ' . $code . '，30分钟内有效,请勿重复获取!如非本人操作，请忽略。';
            $sms->send($mobile, $msg, 'true');
        }
        return true;
    }

    public function codeVerified($mobile, $code, $type = 'register') {
        $mkey = $type . $mobile;
        $item = $this->redis->get($mkey);
        if ($item) {
            return md5($code) == md5($item);
        } else {
            return false;
        }
    }

    public function setTokenUserId($access_token, $user_id) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => intval($user_id)];
        return $this->db->oauth_access_tokens()->where($where)->update($data);
    }

    public function getTokenUserId($access_token) {
        $where = ['access_token' => $access_token];
        $user_id = $this->db->oauth_access_tokens()->where($where)->fetch()['user_id'];
        if ($user_id < 1) {
            return 0;
        }
        return $user_id;
    }

    public function delTokenUserId($access_token) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => 0];
        return $this->db->oauth_access_tokens()->where($where)->update($data);
    }


    public function getAlipayOrder($bizcontent) {
        $alipay = $this->container->get('configs')['alipay'];
        $server = new Server($alipay);
        return $server->getPrePayOrder($bizcontent);
    }

    public function getWechatOrder($bizcontent) {
        $wxpay = $this->container->get('configs')['wxpay'];
        $server = new WxServer($wxpay);
        return $server->getPrePayOrder($bizcontent);
    }

    public function getOauthRequest(){
        $oauthRequest = \OAuth2\Request::createFromGlobals();
        return $oauthRequest->request;
    }

    public function checkMobileIsExists($mobile){
        $where = ['mobile' => $mobile, 'is_del' => 0];
        $result = $this->db->lq_user()->where($where)->fetch();
        if (!$result) {
            return true;
        }
    }

    public function checkUsernameIsExists($username) {
        $where = ['username' => $username];
        $result = $this->db->lq_user()->where($where)->fetch();
        if (!$result) {
            return true;
        }
    }
    
    public function _createPayOrder($user_id,$pay_way,$product_type,$product_id,$total){
        $where['uid'] = $user_id;
        $where['product_type'] =  $product_type;
        $where['product_id'] = $product_id;  
        $where['status'] = 0;
        $orderRow = $this->db->lq_orders($where)->fetch();
        $bodyArr = [1=>'课程报名',2=>'赛事报名',3=>'会员购买'];
        $now = time();
        $createTime = date('Y-m-d H:i:s', $now);
        $orderid = $product_type . $this->buildOrderNo();
        $prepaidOrder = [
            'uid' => $user_id,
            'out_trade_no' => $orderid,
            'total' => $total,
            'pay_way' => $pay_way,
            'product_type' => $product_type, 
            'product_id' => $product_id,
            'ctime' => $now,
            'deadtime' => $now + 30*60
        ];
        if($orderRow){
            $updateRes = $orderRow->update($prepaidOrder);  
        }else{
            $newOrder = $this->operateData('el_orders', $prepaidOrder);
        }
        if($pay_way === 1){
            $bizcontent = [
                'body' => '订单测试',
                'subject' => $bodyArr[$product_type],
                'out_trade_no' => $orderid,
                'timeout_express' => '30m',
                'total_amount' => $total,
    //                'total_amount' => '0.01',
                'pay_way' => $pay_way,
                'type' => $product_type, 
                'product_code' => $product_id,
                'createtime' => $createTime
            ];
        }else if($pay_way === 2){
            $bizcontent = [
                'body' => $bodyArr[$product_type],
                'out_trade_no' => $orderid,
                'total_fee' => intval($total*100),
//                'total_fee' => 1,
                'pay_way' => 2,
                'type' => $product_type, 
                'product_code' => $product_id,
            ];
        }else{
            return false;
        }
        if(!$newOrder && !$updateRes){
            return false;
        }else{
            return $bizcontent;
        }
    }

    public function buildOrderNo() {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    public function operateData($table, $data, $is_update = null, $id = null) {

        if (!$is_update) {
            $data['id'] = 0;
            $insertRes = $this->db->$table()->insert($data);
            if ($insertRes) {
                $insert_id = $this->db->$table()->insert_id();
                return $insert_id;
            } else {
                return FALSE;
            }
        } else {
            $obj = $this->db->$table[$id];
            $rs = $obj->update($data);
            if ($rs) {
                return $rs;
            } else {
                return FALSE;
            }
        }
    }

    public function notFound($req, $res) {
        $data = array();
        $html = $this->view('/404.html', $data);
        $res->getBody()->write($html);
        return $res->withStatus(404, $res->getBody()->getContents());
    }

}
