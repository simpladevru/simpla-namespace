<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 20:25
 */

namespace Root\middleware;

use Root\api\components\design\smarty\AppExtension;
use Root\api\components\design\smarty\RegisterSmartyExtension;
use Root\view\SmartyExtensions\ShopExtension;

class RegisterExtensionsSmartyFromFrontend
{
    public function __invoke($request, $next)
    {
        RegisterSmartyExtension::extensions([
            AppExtension::class,
            ShopExtension::class
        ]);

//        SmartyExtension::extensions([
//            AppExtension::class,
//            BlogExtension::class,
//            BrandExtension::class,
//            ProductExtension::class
//        ]);

        return $next($request);
    }
}