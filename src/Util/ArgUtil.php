<?php

namespace Api\Util;

class ArgUtil
{
    private function __construct()
    {

    }

    /**
     * @param $params 只能是数组或者对象
     */
    static public function extractArg(&$params, $arg, $default = null, $type = null)
    {
        $vals = static::extractArgs($params, (array) $arg, $default, $type);
        return $vals[$arg];
    }

    /**
     * @param $params 只能是数组或者对象
     */
    static public function extractArgs(&$params, array $args, $default = null, $type = null)
    {
        $data = $params;
        $isObject = false;
        if (is_object($params) === true) {
            $isObject = true;
            $data = (array) $params;
        }

        $vals = array();
        foreach ($args as $arg) {
            if ($isObject) {
                unset($params->$arg);
            } else {
                unset($params[$arg]);
            }
            $val = $default;
            if (isset($data[$arg]) === true) {
                $val = $data[$arg];
            }
            switch ($type) {
                case 'int':
                case 'integer':
                    $val = intval($val);
                    break;
                case 'float':
                    $val = floatval($val);
                    break;
                case 'str':
                case 'string':
                    $val = strval($val);
                    break;
                case 'array':
                    $val = (array) $val;
                    break;
                case 'bool':
                case 'boolean':
                    $val = boolval($val);
                    break;
                default:
                    break;
            }
            $vals[$arg] = $val;
        }
        return $vals;
    }

    /**
     * @param $params 只能是数组或者对象
     */
    static public function getArg($params, $arg, $default = null, $type = null)
    {
        $vals = static::getArgs($params, (array) $arg, $default, $type);
        return $vals[$arg];
    }

    /**
     * @param $params 只能是数组或者对象
     */
    static public function getArgs($params, array $args, $default = null, $type = null)
    {
        if (is_object($params) === true) {
            $params = (array) $params;
        }

        $vals = array();
        foreach ($args as $arg) {
            $val = $default;
            if (isset($params[$arg]) === true) {
                $val = $params[$arg];
            }
            switch ($type) {
                case 'int':
                case 'integer':
                    $val = intval($val);
                    break;
                case 'float':
                    $val = floatval($val);
                    break;
                case 'str':
                case 'string':
                    $val = strval($val);
                    break;
                case 'array':
                    $val = (array) $val;
                    break;
                case 'bool':
                case 'boolean':
                    $val = boolval($val);
                    break;
                default:
                    break;
            }
            $vals[$arg] = $val;
        }
        return $vals;
    }
}
