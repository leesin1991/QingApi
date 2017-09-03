<?php
// Routes

$app->any('/', 'Api\Controller\Index:index');
$app->any('/test', 'Api\Controller\V1\Test:index');

$app->group('/v1/oauth', function () {
    $this->post('/register.html', 'Api\Controller\V1\Oauth:register');
    $this->post('/authorize/{auth}.html', 'Api\Controller\V1\Oauth:authorize');
    $this->post('/token/{auth}.html', 'Api\Controller\V1\Oauth:token');
	$this->post('/refresh/{auth}.html', 'Api\Controller\V1\Oauth:refresh');
	// $this->post('/resource.html', 'Api\Controller\V1\Oauth:resource');
});

$app->group('/v1', function () {
    $this->any('/test', 'Api\Controller\V1\Test:index');
    //sendSmsVcode
    $this->post('/sms_code', 'Api\Controller\V1\Sms\Index:sendSmsCode');


})->add(function ($request, $response, $next) {
    $oauthRequest = \OAuth2\Request::createFromGlobals();
    $verifyResponse = $this->oauth2->verifyResourceRequest($oauthRequest);
    if (!$verifyResponse) {
        $body = $this->oauth2->getResponse()->getResponseBody();
        $data = json_decode($body, true);
        if (!$body) {
            $data = '参数错误';
        }
        return $response->withHeader('Content-type', 'application/json')->withJson(array(
            'errno' => 40015,
            'errmsg' => $data
        ));
    }
    return $next($request, $response);
});



// ->add(function ($request, $response, $next) {
//     try{
//         $oauthRequest = \OAuth2\Request::createFromGlobals();
//         $verifyResponse = $this->oauth2->verifyResourceRequest($oauthRequest);
//         if (!$verifyResponse) {
//             $body = $this->oauth2->getResponse()->getResponseBody();
//             $data = json_decode($body, true);
//             return $response->withHeader('Content-type', 'application/json')->withJson(array(
//                 'errno' => 40015,
//                 'errmsg' => $data
//             ));
//         }
//     }catch (\Exception $e) {
        
//         return $response->withJson(array(
//             'errno' => 33,
//             'errmsg' => 33,
//         ));
//     }
//     return $next($request, $response);
// });