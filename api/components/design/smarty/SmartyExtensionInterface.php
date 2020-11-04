<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 19:50
 */

namespace Root\api\components\design\smarty;

/**
 * Interface SmartyExtensionInterface
 *
 * @package Root\api\components\design\smarty
 */
interface SmartyExtensionInterface
{
    public function __construct($container);

    public function register();
}