<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 20:07
 */

namespace Root\api\components\design\smarty;
use Root\api\Container;

/**
 * Class SmartyExtension
 * @package Root\api\components\design\smarty
 *
 * @property \Smarty $smarty
 *
 */
abstract class SmartyExtension
{
    protected $smarty;

    /**
     * SmartyExtension constructor.
     * @param Container $container
     */
    public function __construct($container)
    {
        if(! $container->has('design') || empty($container->design->smarty) ) {
            throw new BadSmartyExtensionException();
        }
        $this->smarty = $container->design->smarty;
    }
}