<?php
// Routes

$app->any('/', 'Api\Controller\Index:index');
$app->any('/test', 'Api\Controller\v1\Test:index');

$app->group('/v1/oauth', function () {
    $this->post('/register.html', 'Api\Controller\v1\Oauth:register');
    $this->post('/authorize/{auth}.html', 'Api\Controller\v1\Oauth:authorize');
    $this->post('/token/{auth}.html', 'Api\Controller\v1\Oauth:token');
	$this->post('/refresh/{auth}.html', 'Api\Controller\v1\Oauth:refresh');
	$this->post('/resource.html', 'Api\Controller\v1\Oauth:resource');
});

$app->group('/v1', function () {
    $this->any('/test', 'Api\Controller\v1\Test:index');




})->add(function ($request, $response, $next) {
    $oauthRequest = \OAuth2\Request::createFromGlobals();
    $verifyResponse = $this->oauth2->verifyResourceRequest($oauthRequest);
    if (!$verifyResponse) {
        $body = $this->oauth2->getResponse()->getResponseBody();
        $data = json_decode($body, true);
        return $response->withHeader('Content-type', 'application/json')->withJson(array(
            'errno' => 40015,
            'errmsg' => $data
        ));
    }
    return $next($request, $response);
});


