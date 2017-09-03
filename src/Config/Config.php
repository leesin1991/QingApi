<?php

namespace Api\Config;

class Config
{
    const DOMAIN = 'http://api.lc';
    const API_VERSION = 'v1';
    const REDIRECT_URL = self::DOMAIN.'/'.self::API_VERSION;
    const AUTHORIZE_REDIRECT_URL = self::DOMAIN.'/'.self::API_VERSION.'/oauth/authorize';
    const ENCRYPT_SECRET = 'QING';
    const API_KEY = 'LEESIN';

    

    private function __construct()
    {

    }
}

