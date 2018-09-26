<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 26.09.2018
 * Time: 15:16
 */

namespace api\facades;

use Root\api\components\facade\Facade;

/**
 * Class Config
 * @package api\facades
 *
 * @method static token($text)
 * @method static check_token($text, $token)
 *
 *  @see \Root\api\Config
 */
class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}