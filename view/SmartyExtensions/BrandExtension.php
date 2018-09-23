<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 20:00
 */

namespace Root\view\SmartyExtensions;

use Root\api\Simpla;

use Root\api\components\design\smarty\BaseExtension;
use Root\api\components\design\smarty\SmartyExtensionInterface;

/**
 * Class BrandExtension
 * @package Root\view\SmartyExtensions
 */
class BrandExtension extends BaseExtension implements SmartyExtensionInterface
{
    public function register()
    {
        $this->smarty->registerPlugin("function", "get_brands", array($this, 'get_brands_plugin'));
    }

    public function get_brands_plugin($params, $smarty)
    {
        if(!empty($params['var'])) {
            $smarty->assign($params['var'], Simpla::$container->brands->get_brands($params));
        }
    }
}