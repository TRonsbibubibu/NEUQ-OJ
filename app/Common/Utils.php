<?php
namespace NEUQOJ\Common;

use Illuminate\Foundation\Testing\TestCase;

/**
 * Created by PhpStorm.
 * User: trons
 * Date: 16/10/12
 * Time: 下午8:49
 */
class Utils
{

    static function createTimeStamp():float
    {
        list($micro, $se) = explode(' ', microtime());
        return $se * 1000 + round($micro, 0);
    }

    public static function IsEmail(string $str):bool
    {
        $patternEmail = '/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/';
        return preg_match($patternEmail, $str) == 1;
    }

    public static function IsMobile(string $str):bool
    {
        $patternMobile = '/(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7}/';
        return preg_match($patternMobile, $str) == 1;
    }
}