<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 24.09.2018
 * Time: 12:37
 */

namespace Root\api\facades;

use Root\api\components\facade\Facade;

/**
 * Class Pages
 * @package Root\api\facades
 *
 * @method static get_page($id)
 * @method static get_pages($filter = array())
 * @method static add_page($page)
 * @method static update_page($id, $page)
 * @method static delete_page($id)
 * @method static get_menus()
 * @method static get_menu($menu_id)
 *
 * @see \Root\api\Pages
 */
class Pages extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pages';
    }
}