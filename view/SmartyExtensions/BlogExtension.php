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
 * Class BlogExtension
 * @package Root\view\SmartyExtensions
 */
class BlogExtension extends BaseExtension implements SmartyExtensionInterface
{
    public function register()
    {
        $this->smarty->registerPlugin("function", "get_posts", array($this, 'get_posts_plugin'));
    }

    public function get_posts_plugin($params, $smarty)
    {
        if(!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        if(!empty($params['var'])) {
            $smarty->assign($params['var'], Simpla::$container->blog->get_posts($params));
        }
    }
}