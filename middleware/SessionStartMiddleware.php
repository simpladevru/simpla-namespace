<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 18.09.2018
 * Time: 18:51
 */

namespace Root\middleware;

class SessionStartMiddleware
{
    public function __invoke($request, $next)
    {
        session_start();
        return $next($request);
    }
}