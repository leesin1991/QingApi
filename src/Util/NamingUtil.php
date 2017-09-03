<?php

namespace Api\Util;

class NamingUtil
{
    protected static $mapping = array(
        'A' => '_a',
        'B' => '_b',
        'C' => '_c',
        'D' => '_d',
        'E' => '_e',
        'F' => '_f',
        'G' => '_g',
        'H' => '_h',
        'I' => '_i',
        'J' => '_j',
        'K' => '_k',
        'L' => '_l',
        'M' => '_m',
        'N' => '_n',
        'O' => '_o',
        'P' => '_p',
        'Q' => '_q',
        'R' => '_r',
        'S' => '_s',
        'T' => '_t',
        'U' => '_u',
        'V' => '_v',
        'W' => '_w',
        'X' => '_x',
        'Y' => '_y',
        'Z' => '_z',
    );

    public static function StudlyCaseToUnderscore($str)
    {
        return strtr(lcfirst($str), static::$mapping);
    }

    public static function UnderscoreToStudlyCase($str)
    {
        return ucwords($str, '_'); 
    }
}
