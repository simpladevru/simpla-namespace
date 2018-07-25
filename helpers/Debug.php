<?php

namespace Root\helpers;

class Debug
{
    public static function print_r($val)
    {
       print "<pre>" . print_r($val, 1) . "</pre>";
    }
}