<?php

/**
 * 对象转数组
 * @param object $obj
 * @return array $result
 */

function toArray($obj) 
{
    foreach ($obj as $row) {
        $result[] = iterator_to_array($row);
    }
    return $result;
}

/**
 * 生成随机数
 * @param number $length
 * @param bool $num
 * @return varchar
 */

function getRandom($length=6,$num=false)
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

/**
 * 验证手机号格式是否合法
 * @param string $mobile
 * @return bool
 */

function validateMobile($mobile)
{
    if (preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\\d{8}$/', $mobile)) {
        return true;
    }   
}

/**
 * 验证邮箱格式是否合法
 * @param string $email
 * @return bool
 */

function validateEmail($email)
{
    if (preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/', $email)) {
        return true;
    }   
}

/**
 * 验证密码长度
 * @param string $password
 * @return bool
 */

function validatePassword($password) {
    if (preg_match('/^(\w){6,16}$/', $password)) {
        return true;
    }
}

/**
 * 验证用户名是否合法 
 * @param string $username
 * @return bool
 */

function validateUsername($username) {
    //只能为英文或数字，且不能以数字打头，长度为6-20个字符
    if (preg_match('/^[a-zA-Z]\w{1,14}$/', $username)) {
        return true;
    }
}

/**
 * 创建多级文件夹 
 * @param string $path 路径 www.domain.com/public/uploads/
 * @param number $mode 权限 755/777 $mode
 */

function mkpath($path, $mode = 0777) {
    $path = str_replace("\\", "_|", $path);
    $path = str_replace("/", "_|", $path);
    $path = str_replace("__", "_|", $path);
    $dirs = explode("_|", $path);
    $path = $dirs[0];
    for ($i = 1; $i < count($dirs); $i++) {
        $path .= "/" . $dirs[$i];        
        if (!is_dir($path)){
            mkdir($path,$mode);
            chmod($path,0777);
        }
    }
}

/*
 * 密码加密
 */
function encryptPassword($password, $salt) {
    return md5(md5($password).$salt);
}

/*
 * 过滤非法html标签
 */
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

/**
 * 获取手机客户端类型
 */

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



/**
 * utf-8、gb2312都支持的汉字截取函数
 * @param string $string 待截取字符串
 * @param number $sublen 截取长度
 * @param number $start 开始长度
 * @param string $code 编码格式
 * @return string tmpstr
 */

function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
{
    if($code == 'UTF-8')
    {
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);
 
        if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen))."...";
        return join('', array_slice($t_string[0], $start, $sublen));
    }
    else
    {
        $start = $start*2;
        $sublen = $sublen*2;
        $strlen = strlen($string);
        $tmpstr = '';
 
        for($i=0; $i< $strlen; $i++)
        {
            if($i>=$start && $i< ($start+$sublen))
            {
                if(ord(substr($string, $i, 1))>129)
                {
                    $tmpstr.= substr($string, $i, 2);
                }
                else
                {
                    $tmpstr.= substr($string, $i, 1);
                }
            }
            if(ord(substr($string, $i, 1))>129) $i++;
        }
        if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
        return $tmpstr;
    }
}

/**
 * php缩放图片
 * @param string $filename
 * @param string $tmpname
 * @param number $xmax
 * @param number $ymax
 * @return img
 */
function resize_image($filename, $tmpname, $xmax, $ymax)  
{  
    $ext = explode(".", $filename);  
    $ext = $ext[count($ext)-1];  
         
    if($ext == "jpg" || $ext == "jpeg")  
        $im = imagecreatefromjpeg($tmpname);  
    elseif($ext == "png")  
        $im = imagecreatefrompng($tmpname);  
    elseif($ext == "gif")  
        $im = imagecreatefromgif($tmpname);  
             
    $x = imagesx($im);  
    $y = imagesy($im);  
             
    if($x <= $xmax && $y >= $ymax)  
        return $im;  
         
    if($x >= $y) {  
        $newx = $xmax;  
        $newy = $newx * $y / $x;  
    }  
    else {  
        $newy = $ymax;  
        $newx = $x / $y * $newy;  
    }  
             
    $im2 = imagecreatetruecolor($newx, $newy);  
    imagecopyresized($im2, $im, 0, 0, 0, 0, floor($newx), floor($newy), $x, $y);  
    return $im2;   
}


/**
 * 数组转xml
 * @param array $arr 待转的数组
 * @return xml $xml处理后内容
 */

function arrayToXml($arr) {
    $xml = "<xml>";
    foreach ($arr as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
    }
    $xml .= "</xml>";
    return $xml;
}

/**
 * h函数用于过滤不安全的html标签，输出安全的html
 * @param string $text 待过滤的字符串
 * @param string $type 保留的标签格式
 * @return string 处理后内容
 */

function h($text, $type = 'html')
{
    // 无标签格式
    $text_tags = '';
    //只保留链接
    $link_tags = '<a>';
    //只保留图片
    $image_tags = '<img>';
    //只存在字体样式
    $font_tags = '<i><b><u><s><em><strong><font><big><small><sup><sub><bdo><h1><h2><h3><h4><h5><h6>';
    //标题摘要基本格式
    $base_tags = $font_tags . '<p><br><hr><a><img><map><area><pre><code><q><blockquote><acronym><cite><ins><del><center><strike>';
    //兼容Form格式
    $form_tags = $base_tags . '<form><input><textarea><button><select><optgroup><option><label><fieldset><legend>';
    //内容等允许HTML的格式
    $html_tags = $base_tags . '<meta><ul><ol><li><dl><dd><dt><table><caption><td><th><tr><thead><tbody><tfoot><col><colgroup><div><span><object><embed><param>';
    //专题等全HTML格式
    $all_tags = $form_tags . $html_tags . '<!DOCTYPE><html><head><title><body><base><basefont><script><noscript><applet><object><param><style><frame><frameset><noframes><iframe>';
    //过滤标签
    $text = real_strip_tags($text, ${$type . '_tags'});
    // 过滤攻击代码
    if ($type != 'all') {
        // 过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+)(ondblclick|onclick|onload|onerror|unload|onmouseover|onmouseup|onmouseout|onmousedown|onkeydown|onkeypress|onkeyup|onblur|onchange|onfocus|action|background|codebase|dynsrc|lowsrc)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_ireplace($mat[0], $mat[1] . $mat[3], $text);
        }
    }
    return $text;
}