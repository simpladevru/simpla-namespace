<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 24.09.2018
 * Time: 12:39
 */

namespace Root\api\components\facade;

use Root\api\Simpla;

class Facade
{
    public static function __callStatic($method, $args)
    {
        $instance = Simpla::$container->{static::getFacadeAccessor()};

        if (! $instance) {
            throw new \Exception('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}