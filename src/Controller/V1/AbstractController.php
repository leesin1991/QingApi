<?php

namespace Api\Controller\V1;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;
use Api\Error\ErrorCode;
 
abstract class AbstractController 
{
    protected $container;

    protected $db;

    protected $view;

    protected $redis;

    protected $mongo;

    protected $oauth2;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->db = $container->get('db');
        $this->redis = $container->get('redis');
        $this->mongo = $container->get('mongodb'); 
        $this->oauth2 = $container->get('oauth2'); 
        $this->view = $container->get('renderer');
        // print_r($this->db);die;
    }
   
    protected function jsonSuccess(Response $response, $data = null, $message = '')
    {
        $result = [
            'errno' => ErrorCode::SUCCESS,
        ];
        if ($data !== null) {
            $result['data'] = $data;
        } else {
            $result['message'] = $message;
        }
        return $response->withHeader('Content-type', 'application/json')->withJson($result);
    }

    protected function jsonError(Response $response, $errno, $defaultErrmsg = null, $data = null)
    {
        
        $errmsg = '出错了';
        if (!$errno) {
            $errno = ErrorCode::LOGIC_ERROR;
        }
        if ($defaultErrmsg) {
            $errmsg = $defaultErrmsg;
        }
        $result = [
            'errno' => $errno,
            'errmsg' => $errmsg,
        ];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return $response->withHeader('Content-type', 'application/json')->withJson($result);
    }

    public function __invoke(Request $request)
    {
        $action = 'http' . ucfirst(strtolower($request->getMethod()));
        return call_user_func_array([$this, $action], func_get_args());
    }

}
