<?php

namespace Api\Controller\V1\Pay;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Api\Controller\V1\Controller;

class AliPay extends Controller
{

    protected function aliPay() {
        $post = $this->post;
        if ($post['total'] && $post['type'] && $post['product_id']) {
            $user_id = $this->app->getTokenUserId($post['access_token'], $post['client_id']);
            if($user_id < 1 ){
                return ['status' => false, 'errno' =>'41001','errmsg' => "前先登录！" ];
            }
            $type = intval($post['type']);
            $isPaid = $this->app->no()->el_orders()->where(['uid'=>$user_id,'product_type'=>$type,'product_id'=>$post['product_id'],'status'=>1])->fetch();
            if($isPaid){
                return ['status' => false, 'errno' => '42014', 'errmsg' => "您已购买过"];
            }
            $beforePayCheckRes = $this->_createPayOrder($user_id, 1, $type, $post['product_id'],$post['total']);
            if($beforePayCheckRes){
                $aliOrder = $beforePayCheckRes;
            }else{
                return ['status' => false, 'errno' => '-1', 'errmsg' => "系统繁忙，请稍后再试！"];
            }   
//            print_r($aliOrder);die;
            $bizcontent = json_encode($aliOrder);
            $return = $this->app->getAlipayOrder($bizcontent);  
            $data = ['order'=>$return];
            return ['status' => true, 'errno' => '0', 'data' => $data];
        } else {
            return array('status' => false, 'errno' => '40001', 'errmsg' => "请求参数无效");
        }
    }

        public function _createPayOrder($user_id,$pay_way,$product_type,$product_id,$total){
        $where['uid'] = $user_id;
        $where['product_type'] =  $product_type;
        $where['product_id'] = $product_id;  
        $where['status'] = 0;
        $orderRow = $this->app->no()->el_orders($where)->fetch();
        $bodyArr = [1=>'青训课程报名',2=>'赛事报名',3=>'会员购买'];
        $now = time();
        $createTime = date('Y-m-d H:i:s', $now);
        $orderid = $product_type . $this->buildOrderNo();
        $prepaidOrder = [
            'uid' => $user_id,
            'out_trade_no' => $orderid,
            'total' => $total,
            'pay_way' => $pay_way,
            'product_type' => $product_type, 
            'product_id' => $product_id,
            'ctime' => $now,
            'deadtime' => $now + 30*60
        ];
        if($orderRow){
            $updateRes = $orderRow->update($prepaidOrder);  
        }else{
            $newOrder = $this->operateData('el_orders', $prepaidOrder);
        }
        if($pay_way === 1){
            $bizcontent = [
                'body' => '树苗足球',
                'subject' => $bodyArr[$product_type],
                'out_trade_no' => $orderid,
                'timeout_express' => '30m',
                'total_amount' => $total,
    //                'total_amount' => '0.01',
                'pay_way' => $pay_way,
                'type' => $product_type, 
                'product_code' => $product_id,
                'createtime' => $createTime
            ];
        }else if($pay_way === 2){
            $bizcontent = [
                'body' => $bodyArr[$product_type],
                'out_trade_no' => $orderid,
                'total_fee' => intval($total*100),
//                'total_fee' => 1,
                'pay_way' => 2,
                'type' => $product_type, 
                'product_code' => $product_id,
            ];
        }else{
            return false;
        }
        if(!$newOrder && !$updateRes){
            return false;
        }else{
            return $bizcontent;
        }
    }


}
