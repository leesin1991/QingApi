<?php

namespace Api\Controller\v1;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\lib\Sms;
use Api\lib\Alipay\Server;
use Api\lib\Wxpay\WxServer;

class Controller extends AbstractController
{

    public function sendSmsVcode($mobile) {
        $mkey = 'm' . $mobile;
        $item = $this->redis->get($mkey);
        if (!$item) {
            $code = mt_rand(100000, 999999);
            $this->redis->setex($mkey, 60 * 10, $code);
            $smsConfig = $this->container->get('configs')['sms'];
            $sms = new Sms($smsConfig);
            $msg = $smsConfig['sign'].'注册验证码： ' . $code . '，十分钟内有效,请勿重复获取!如非本人操作，请忽略。';
            $sms->send($mobile, $msg, 'true');
        }
        return true;
    }

    public function codeVerified($mobile, $code) {
        $mkey = 'm' . $mobile;
        $item = $this->redis->get($mkey);
        if ($item) {
            return md5($code) == md5($item);
        } else {
            return false;
        }
    }

    public function setTokenUserId($access_token, $client_id, $user_id) {
        $where = ['access_token' => $access_token, 'client_id' => $client_id];
        $data = ['user_id' => intval($user_id)];
        return $this->db->oauth_access_tokens()->where($where)->update($data);
    }

    public function getTokenUserId($access_token, $client_id) {
        $where = ['access_token' => $access_token, 'client_id' => $client_id];
        $user_id = $this->db->oauth_access_tokens()->where($where)->fetch()['user_id'];
        if ($user_id < 1) {
            return 0;
        }
        return $user_id;
    }

    public function delTokenUserId($access_token, $client_id) {
        $where = ['access_token' => $access_token, 'client_id' => $client_id];
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

    public function notFound($req, $res) {
        $data = array();
        $html = $this->view('/404.html', $data);
        $res->getBody()->write($html);
        return $res->withStatus(404, $res->getBody()->getContents());
    }

}
