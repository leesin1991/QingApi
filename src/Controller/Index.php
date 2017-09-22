<?php

namespace Api\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Api\Controller\V1\Controller;
use Intervention\Image\ImageManagerStatic as Image;

class Index extends Controller{

    public function index(Request $request, Response $response)
    {
        // echo phpinfo();die;
        //echo base64_encode(random_bytes(32));die; //wwTCGJizEE9W0BBonTbOM78yeJcDc7LlDohKVOSQm+s=
        // print_r($this->container);die;
        // $this->sendSmsVcode('15900545092');die;

        // $bizcontent = [
        //     'body' => '测试',
        //     'out_trade_no' => '12312312312313',
        //     'total_fee' => 1,
        //     'pay_way' => 2,
        //     'type' => 1, 
        //     'product_code' => 1,
        // ];
        // $return = $this->getWechatOrder($bizcontent); 
        // print_r($return);die;

    	// $now = date('Y-m-d H:i:s', time());
     //    $order = [
     //        'body' => '树苗足球-赛事报名',
     //        'subject' => '赛事报名',
     //        'out_trade_no' => '12312312311',
     //        'timeout_express' => '30m',
     //        'total_amount' => '0.01',
     //        'payment' => 1,
     //        'type' => 1, 
     //        'product_code' => 1,
     //        'createtime' => $now
     //    ];
     //    $bizcontent = json_encode($order);
     //    $return = $this->getAlipayOrder($bizcontent);  
     //    print_r($return);die;
          
        // $redis = $this->db;
        // print_r($redis);die;
        // echo md5(123456);die;
        // $rs = $this->db->clients()->select('');
        // $data = toArray($rs);
        // print_r($data);

        // $manager = new ImageManager(array('driver' => 'gd'));
        // $image = $manager->make('upload/img/tu1.jpg')->resize(300, 200);
        // Image::configure(array('driver' => 'gd'));
        // $image = Image::make('upload/img/tu1.png')->resize(300, 200)->save('upload/img/tu1.png');;
        // return $image->response('jpg');
        // print_r($image);
        die('Permission denied!');

    }

}
