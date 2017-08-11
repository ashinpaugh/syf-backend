<?php

namespace Moop\Bundle\HealthBundle\Util;

/**
 * @see http://php.net/manual/en/function.base64-encode.php
 */
class Str
{
    public static function base64UrlEncode($data)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }
        
        if (!static::isBase64Encoded($data))  {
            $data = base64_encode($data);
        }
        
        return rtrim(strtr($data, '+/', '-_'), '=');
    }
    
    public static function base64UrlDecode($data)
    {
        return json_decode(base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)), true);
    }
    
    public static function isBase64Encoded($data)
    {
        return $data === base64_encode(base64_decode($data, true));
    }
}