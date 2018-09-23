<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 19:41
 */

namespace Root\api\components\design\smarty;

use Root\api\Simpla;

class SmartyExtension
{
    public static function add($extension)
    {
        if(! class_exists($extension) ) {
            return false;
        }

        $extension = new $extension(Simpla::$container->design->smarty);

        if(! $extension instanceof SmartyExtensionInterface ) {
            return false;
        }

        $extension->register();
    }
}