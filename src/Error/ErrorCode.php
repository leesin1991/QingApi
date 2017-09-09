<?php

namespace Api\Error;

class ErrorCode
{
    const SUCCESS = 0;

    /**
     * 用于系统调试（不宜向用户公开）的错误代码为负数 < 0
     */
    const DEBUG_ERROR = -1;

    /**
     * 用于业务逻辑提示（向用户公开）的错误代码为正数 > 0
     *
     */
    const LOGIC_ERROR = 1;
    const LOGIC_ERROR_INVALID_ACCESS_TOKEN = 2;
    const LOGIC_ERROR_INVALID_REFRESH_TOKEN = 3;
    const LOGIC_ERROR_USER_NOT_FOUND = 4;
    const LOGIC_ERROR_BALANCE_NOT_ENOUGH = 5;

    private function __construct()
    {

    }
}

