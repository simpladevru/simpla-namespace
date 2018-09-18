<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 18.09.2018
 * Time: 18:52
 */

namespace Root\middleware;

class LogoutMiddleware
{
    public function __invoke($request, $next)
    {
        if(isset($_GET['logout']))
        {
            header('WWW-Authenticate: Basic realm="Simpla CMS"');
            header('HTTP/1.0 401 Unauthorized');
            unset($_SESSION['admin']);
        }

        return $next($request);
    }
}