<?php

namespace Api\V1\Config;

class Config
{
	const ROOT_PATH = dirname(dirname(__FILE__));
    const DOMAIN = 'http://qing.lc';
    const API_VERSION = 'v1';
    const REDIRECT_URL = self::DOMAIN.'/'.self::API_VERSION;
    const AUTHORIZE_REDIRECT_URL = self::DOMAIN.'/'.self::API_VERSION.'/oauth/authorize';
    const ENCRYPT_SECRET = 'QING';
    const API_KEY = 'LEESIN';

    

    private function __construct()
    {

    }
}