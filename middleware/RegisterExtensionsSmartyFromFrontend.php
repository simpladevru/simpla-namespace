<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 20:25
 */

namespace Root\middleware;

use Root\api\components\design\smarty\AppExtension;
use Root\api\components\design\smarty\SmartyExtension;
use Root\view\SmartyExtensions\ShopExtension;

class RegisterExtensionsSmartyFromFrontend
{
    public function __invoke($request, $next)
    {
        SmartyExtension::extensions([
            AppExtension::class,
            ShopExtension::class
        ]);

        return $next($request);
    }
}