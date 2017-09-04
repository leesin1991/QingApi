<?php

function toArray($obj) 
{
    foreach ($obj as $row) {
        $result[] = iterator_to_array($row);
    }
    return $result;
}

function getRandom($length=6,$num=null)
{
    $str = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $s = null;
    if($num)$str = "0123456789";
    $len = strlen($str)-1;
    for($i=0 ; $i<$length; $i++){
        $s .=  $str[rand(0,$len)];
    }
    return $s;
}

function validateMobile($mobile)
{
    if (preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\\d{8}$/', $mobile)) {
        return true;
    }   
}

function validatePassword($password) {
    if (preg_match('/^(\w){6,16}$/', $password)) {
        return true;
    }
}

function validateUsername($username) {
    //只能为英文或数字，且不能以数字打头，长度为6-20个字符
    if (preg_match('/^[a-zA-Z]\w{1,14}$/', $username)) {
        return true;
    }
}

function mkpath($path, $mode = 0777) {
    $path = str_replace("\\", "_|", $path);
    $path = str_replace("/", "_|", $path);
    $path = str_replace("__", "_|", $path);
    $dirs = explode("_|", $path);
    $path = $dirs[0];
    for ($i = 1; $i < count($dirs); $i++) {
        $path .= "/" . $dirs[$i];        
        if (!is_dir($path)){
            mkdir($path);
            chmod($path,0777);
        }
    }
}

function t($text) {
    $text = nl2br($text);
    $text = real_strip_tags($text);
    $text = addslashes($text);
    $text = trim($text);
    return addslashes($text);
}

function real_strip_tags($str, $allowable_tags = "") {
    $str = stripslashes(htmlspecialchars_decode($str));
    return strip_tags($str, $allowable_tags);
}

function getHttpUserAgent() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
        $agent = 'iOS';
    } else if (strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
        $agent = 'Android';
    } else {
        $agent = 'Other';
    }
    return $agent;
}