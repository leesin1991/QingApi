<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Api\lib;

class Sms {

    private $config;

    public function __construct(array $config){
        $this->config = $config;
    }

    public function send($mobile, $msg, $needstatus = 'true') {
        $postArr = array(
            'account' => $this->config['api_account'],
            'password' => $this->config['api_password'],
            'msg' => urlencode($msg),
            'phone' => $mobile,
            'report' => $needstatus
        );
        $result = $this->curlPost($this->config['api_send_url'], $postArr);
        return $result;
    }

    public function sendParams($msg, $params) {
        $postArr = array(
            'account' => $this->config['api_account'],
            'password' => $this->config['api_password'],
            'msg' => $msg,
            'params' => $params,
            'report' => 'true'
        );
        $result = $this->curlPost($this->config['api_variable_url'], $postArr);
        return $result;
    }

    public function queryBalance() {
        $postArr = array(
            'account' => $this->config['api_account'],
            'password' => $this->config['api_password'],
        );
        $result = $this->curlPost($this->config['api_balance_query_url'], $postArr);
        return $result;
    }

    private function curlPost($url, $post) {
        $encode = json_encode($post);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8'
                )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encode);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $msg = json_decode($ret, true);
            if ($msg['code'] + 0 === 0) {
                $result = true;
            } else {
                $result = false;
            }
        }
        curl_close($ch);
        return $result;
    }

}
