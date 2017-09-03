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
