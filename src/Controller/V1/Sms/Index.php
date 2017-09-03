<?php

namespace Api\Controller\V1\Sms;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;

class Index extends Controller
{

    public function sendSmsCode(Request $request, Response $response)
    {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
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



}
