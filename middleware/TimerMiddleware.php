<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 18.09.2018
 * Time: 18:50
 */

namespace Root\middleware;

class TimerMiddleware
{
    public function __invoke($request, $next)
    {
        // Засекаем время
        $time_start = microtime(true);

        $result = $next($request);

        // Отладочная информация
        if(1)
        {
            print "<!--\r\n";
            $time_end = microtime(true);
            $exec_time = $time_end-$time_start;

            if(function_exists('memory_get_peak_usage')) {
                print "memory peak usage: ".memory_get_peak_usage()." bytes\r\n";
            }
            print "page generation time: ".$exec_time." seconds\r\n";
            print "-->";
        }

        return $result;
    }
}