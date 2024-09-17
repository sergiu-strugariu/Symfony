<?php

namespace App\Helper;

class DefaultHelper
{
    public static function generateHash($string): string
    {
        return sha1(mt_rand(1, 90000) . $string);
    }
}