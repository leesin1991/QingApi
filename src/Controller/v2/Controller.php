<?php

namespace Api\Controller\v1;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

class Controller extends AbstractController
{
  
    private static $db;
    private static $cache;
    private static $pdo;
    protected static $ssid = null;
    protected static $authed = false;
    protected $cookie = array();
    protected $clientIp = '127.0.0.1';
    protected $userAgent = '';

    public function db() {
        if (self::$db == null) {
            self::$db = $this->container->get('notorm');
        }
        return self::$db;
    }

    public function cache() {
        if (self::$cache == null) {
            $redis = $this->get('redis');
            self::$cc = $redis;
        }
        return self::$cc;
    }


    public function pdo() {
        if (self::$pdo == null) {
            self::$pdo = new \PDO('mysql:host=localhost;dbname=shared', 'root', '7&8&8^67HkjU', array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
        }
        return self::$pdo;
    }

    

    public function authed() {
        if (self::$authed == false) {
            $authed = $this->ssget('authed');
            if ($authed) {
                self::$authed = $authed;
            } else {
                self::$authed = false;
            }
        }
        return self::$authed;
    }


    public function toArray($obj) 
    {
        foreach($obj as $row) {
            $ret[] = iterator_to_array($row); 
        }
        return $ret;
    }

    public function getRandom($length=6,$num=null)
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
    
    public function validateMobile($mobile)
    {
        if (preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|7[0135678]|8[0-9])\\d{8}$/', $mobile)) {
            return true;
        }   
    }


}
