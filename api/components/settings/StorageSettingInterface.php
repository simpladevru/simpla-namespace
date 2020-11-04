<?php

namespace Root\api\components\settings;

/**
 * Interface StorageSettingInterface
 *
 * @package Root\api\components\settings
 */
interface StorageSettingInterface
{
    public function get($name);

    public function set($name, $value);

    public function has($name);
}