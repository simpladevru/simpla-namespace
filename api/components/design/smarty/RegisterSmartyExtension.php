<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 19:41
 */

namespace Root\api\components\design\smarty;

use Root\api\Simpla;
use Root\helpers\Debug;

/**
 * Class RegisterSmartyExtension
 *
 * @package Root\api\components\design\smarty
 */
class RegisterSmartyExtension
{
    /**
     * @param SmartyExtensionInterface $extension
     * @return bool
     */
    public static function add($extension)
    {
        if (!class_exists($extension)) {
            return false;
        }

        $extension = new $extension(Simpla::$container);

        if (!$extension instanceof SmartyExtensionInterface) {
            throw new BadSmartyExtensionException();
        }

        $extension->register();
    }

    /**
     * @param SmartyExtensionInterface[] $extensions
     */
    public static function extensions($extensions = [])
    {
        foreach ($extensions as $extension) {
            static::add($extension);
        }
    }
}