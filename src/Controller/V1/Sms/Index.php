<?php

namespace Api\Controller\V1\Sms;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;

class Index extends Controller
{

    public function sendCode(Request $request, Response $response)
    {
        $post = $this->getOauthRequest();
        if ($post['mobile']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
            	return $this->jsonError($response,40012,'手机格式错误');
            }
            $rs = $this->sendSmsVcode($post['mobile']);
            if ($rs) {
            	return $this->jsonSuccess($response,null,'验证码已发送，请注意查收');
            } else {
                return $this->jsonError($response,40012,'发送失败');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
        return $return;
    }

    public function verifyCode(Request $request, Response $response) {
        $post = $this->getOauthRequest();
        if ($post['mobile'] && $post['vericode']) {
            $mobileValidate = validateMobile($post['mobile']);
            if (!$mobileValidate) {
            	return $this->jsonError($response,40012,'手机格式错误');
            }
            $vericodeCheck = $this->codeVerified($post['mobile'], $post['vericode']);
            if (!$vericodeCheck) {
                return $this->jsonError($response,40012,'验证码错误');
            } else {
            	return $this->jsonSuccess($response,null,'验证成功');
            }
        } else {
            return $this->jsonError($response,40012,'请求参数错误');
        }
    }


}
