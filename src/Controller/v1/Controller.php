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
    
    public function buildOrderNo() {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    public function notFound($req, $res) {
        $data = array();
        $html = $this->view('/404.html', $data);
        $res->getBody()->write($html);
        return $res->withStatus(404, $res->getBody()->getContents());
    }

}
