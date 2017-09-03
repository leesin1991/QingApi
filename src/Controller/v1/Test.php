<?php

namespace Api\Controller\v1;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class Test extends Controller
{

    public function index(Request $request, Response $response)
    {
        //echo base64_encode(random_bytes(32));die; //wwTCGJizEE9W0BBonTbOM78yeJcDc7LlDohKVOSQm+s=
        
        // echo phpinfo();die;
        // echo md5(123456);
        $rs = $this->db->user()->select('');
        $data = $this->toArray($rs);
        print_r($data);
    }



}
