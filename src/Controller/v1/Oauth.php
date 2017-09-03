<?php

namespace Api\Controller\v1;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Config\Config;

class Oauth extends Controller
{

    public function register(Request $req, Response $res, $args) {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        $storage = $this->oauth2->getStorage('client');
        if (isset($post['imei']) && isset($post['code'])) {
            if (md5($post['imei']) == $post['code']) {
                $client_id = md5($post['imei']);
                $client_secret = md5(Config::ENCRYPT_SECRET.uniqid());
                $status = $storage->setClientDetails($client_id, $client_secret, Config::AUTHORIZE_REDIRECT_URL,'authorization_code');
                if ($status) {
                    $details = $storage->getClientDetails($client_id);
                    $data = $this->client($details);
                    return $this->jsonSuccess($res,$data);
                } else {
                    return $this->jsonError($res,40002,'设置失败');
                }
            } else {
                return $this->jsonError($res,40003,'请求参数不合法');
            }
        } else {
            return $this->jsonError($res,40001,'请求参数缺失');
        }
    }


    public function authorize(Request $req, Response $res, $args) {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'authorize'), 0, 8)) {
            $response = new \OAuth2\Response();
            if ($this->oauth2->validateAuthorizeRequest($request, $response)) {
                $clientId = 0;
                $this->oauth2->handleAuthorizeRequest($request, $response, true, $clientId);
                $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
                $authorize = $this->oauth2->getStorage('authorization_code');
                $authed = $authorize->getAuthorizationCode($code);
                $data = [
                    'authorize_code' => $code,
                    'expire_time' => $authed['expires']
                ];
                return $this->jsonSuccess($res,$data);
            } else {
                return $this->jsonError($res,40004,'授权失败');
            }
        } else {
            return $this->jsonError($res,40003,'请求参数不合法');
        }
    }

    /*
     * 有效期为 1209600s，可以在 OAuth2/ResponseType/AccessToken.php 中的 AccessToken class 中的构造函数配置中进行修改。
     * curl -u app_key:app_secret /authed/token/********.html -d grant_type=authorization_code&code=$authcode
     */

    public function token(Request $req, Response $res, $args) {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'token'), 0, 8)) {
            $response = new \OAuth2\Response();
            $resp = $this->oauth2->handleTokenRequest(\OAuth2\Request::createFromGlobals(), $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                return $this->jsonSuccess($res,$data);
            } else {
                return $this->jsonError($res,40005,$data);
            }
        } else {
            return $this->jsonError($res,40003,'请求参数不合法');
        }
    }


    /*
     * curl -u app_key:app_secret /authed/refresh/********.html -d "grant_type=refresh_token&refresh_token=xxx"
     */

    public function refresh(Request $req, Response $res, $args) {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'refresh'), 0, 8)) {     
            $response = new \OAuth2\Response();
            $resp = $this->oauth2->handleTokenRequest(\OAuth2\Request::createFromGlobals(), $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                return $this->jsonSuccess($res,$data);
            } else {
                return $this->jsonError($res,40005,$data);
            }
        } else {
            return $this->jsonError($res,40003,'请求参数不合法');
        }
    }

    /*
     * curl /authed/resource/********.html -d access_token=xxx
     */

    public function resource(Request $req, Response $res, $args) {
        $request = \OAuth2\Request::createFromGlobals();
        $request->request;
        if (!$this->oauth2->verifyResourceRequest($request)) {
            $body = $this->oauth2->getResponse()->getResponseBody();
            $data = json_decode($body, true);
            return $this->jsonError($res,40005,$data);
        }
        // else{
        //     return $this->jsonSuccess($res);
        // }
    }

    public function client($details) {
        $seed = md5(uniqid());
        $authorize = substr(md5($details['client_id'] . $seed . 'authorize'), 0, 8);
        $token = substr(md5($details['client_id'] . $seed . 'token'), 0, 8);
        $refresh = substr(md5($details['client_id'] . $seed . 'refresh'), 0, 8);
        $resource = substr(md5($details['client_id'] . $seed . 'resource'), 0, 8);
        return array(
            'app_key' => $details['client_id'],
            'app_secret' => $details['client_secret'],
            'authorize_url' => Config::REDIRECT_URL.'/oauth/authorize/'.$authorize.'.html',
            'token_url' => Config::REDIRECT_URL.'/oauth/token/'. $token.'.html',
            'refresh_url' => Config::REDIRECT_URL.'/oauth/refresh/'.$refresh.'.html',
            // 'source_url' => Config::REDIRECT_URL.'/oauth/resource/'.$resource.'.html',
            'state' => $seed,
            'expire_time' => 30
        );
    }










    //以下为待开发代码

    public function clientGet($details) {
        $seed = md5(uniqid().Config::ENCRYPT_SECRET);
        $authorize = substr(md5($details['client_id'] . $seed . 'authorize'), 0, 8);
        $token = substr(md5($details['client_id'] . $seed . 'token'), 0, 8);
        $refresh = substr(md5($details['client_id'] . $seed . 'refresh'), 0, 8);
        $resource = substr(md5($details['client_id'] . $seed . 'resource'), 0, 8);
        return array(
            'app_key' => $details['client_id'],
            'app_secret' => $details['client_secret'],
            'authorize_url' => Config::REDIRECT_URL.'/oauth/authorize/'.$authorize.'.html',
            'token_url' => Config::REDIRECT_URL.'/oauth/token/'. $token.'.html',
            'refresh_url' => Config::REDIRECT_URL.'/oauth/refresh/'.$refresh.'.html',
            'source_url' => Config::REDIRECT_URL.'/oauth/resource/'.$resource.'.html',
            'seed_secret' => $seed,
            'expire_time' => 30
        );
    }

    public function getSign($Obj) {
        foreach ($Obj as $k => $v) {
            $Parameters[strtolower($k)] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo "【string】 =".$String."</br>";
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $this->config['api_key'];
        //echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
        //签名步骤三：MD5加密
        $result_ = strtoupper(md5($String));
        return $result_;
    }

    public function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= strtolower($k) . "=" . $v . "&";
        }
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}
