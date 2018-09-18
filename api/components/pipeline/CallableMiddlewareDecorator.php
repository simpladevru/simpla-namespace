<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 18.09.2018
 * Time: 18:56
 */

namespace Root\api\components\pipeline;

class CallableMiddlewareDecorator
{
    private $middleware;

    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    public function handle($request, $next)
    {
        return ($this->middleware)($request, $next);
    }
}