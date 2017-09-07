<?php

namespace Api\Controller\V1\Pay;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;
use Api\lib\Alipay\Server;

class Index extends Controller
{

    public function aliPay(Request $req, Response $res) 
    {
        $post = $req->getParsedBody();
        $server = new Server();
        $flag = $server->notify($post);
        if($flag) {
            $data = json_encode($post);
            $path = dirname(dirname(dirname(dirname(__FILE__))))."/var/logs/aliorder.txt";
            chmod($path, 0777);
            file_put_contents($path, $data.PHP_EOL, FILE_APPEND);
            $where['out_trade_no'] = $post['out_trade_no'];
            $where['is_del'] = 0;
            $where['status'] = 0;
            $orderRow = $this->db->lq_orders()->where($where)->fetch();
            $orderData = [
                'trade_no' => $post['trade_no'],
                'total' => $post['total_amount'],
                'pay_time' => strtotime($post['gmt_create']),
            ];
            if ($post['trade_status'] === 'TRADE_SUCCESS') {
                $orderData['status'] = 1;
            }else{
                $orderData['status'] = 0;
            }
            if($orderRow){
                $orderUpdateRes = $orderRow->update($orderData);
            }else{
                return false;
            }
            //其他数据更新
            if ($orderUpdateRes) {
                echo "success";
            }
        } else {
            return false;
        }
    }

    public function wxNotifyPost(Request $req, Response $res, $args) { 
        $xml = file_get_contents('php://input');
        $return = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);  
        if($return) {
            $data = json_encode($return);
            $path = dirname(dirname(dirname(dirname(__FILE__))))."/var/logs/wxorder.txt";
            chmod($path, 0777);
            file_put_contents($path, $data.PHP_EOL, FILE_APPEND);
            $where['out_trade_no'] = $return['out_trade_no'];
            $where['is_del'] = 0;
            $where['status'] = 0;
            $orderRow = $this->db->lq_orders()->where($where)->fetch();
            
            //验签（待完善)
//            $sign = http_build_query($return);
//            $sign = md5($sign);
//            $sign = strtoupper($sign);
//            if ( $sign === $return['sign']) {
//                if($orderRow['total'] == $return['total_fee']/100){
//                    
//                }else{
//                    $return = ['errmsg'=>'金额发生修改'];
//                }
//            } else {
//                return false;
//            }
            
            $orderData = [
                'trade_no' => $return['transaction_id'],
                'total' => $return['total_fee']/100,
                'pay_time' => strtotime($return['time_end']),
            ];
            if ($return['result_code'] === 'SUCCESS') {
                $orderData['status'] = 1;
            }else{
                $orderData['status'] = 0;
            }
            if($orderRow){
                $orderUpdateRes = $orderRow->update($orderData);
            }else{
                return false;
            }
            //其他数据更新
            if ($orderUpdateRes) {
                $returnToWx = ['return_code'=>'SUCCESS','return_msg'=>'OK'];
                $xml = '<xml>';
                foreach($returnToWx as $k=>$v){
                    $xml.='<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
                }
                $xml.='</xml>';
                echo $xml;
            }
        } else {
            return false;
        }
    }
    
    

}
