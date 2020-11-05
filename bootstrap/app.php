<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Root\api\Simpla);

if( !function_exists('simpla') )
{
    /**
     * @param null $api
     * @return \Root\api\Simpla|\Root\api\Container
     * @throws ReflectionException
     */
    function simpla($api = null)
    {
        if(! is_null($api) ) {
            return Root\api\Simpla::$container->get($api);
        }
        return Root\api\Simpla::$container;
    }
}

if( !function_exists('design') )
{
    /**
     * @return \Root\api\Design
     */
    function design()
    {
        return simpla('design');
    }
}

if( !function_exists('settings') )
{
    /**
     * @return \Root\api\Settings
     */
    function settings()
    {
        return simpla('settings');
    }
}

if( !function_exists('config') )
{
    /**
     * @return \Root\api\Config
     */
    function config()
    {
        return simpla('config');
    }
}

if( !function_exists('request') )
{
    /**
     * @return \Root\api\Request
     */
    function request()
    {
        return simpla('request');
    }
}

if( !function_exists('db') )
{
    /**
     * @return \Root\api\Database
     */
    function db()
    {
        return simpla('db');
    }
}