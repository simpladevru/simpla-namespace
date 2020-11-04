<?php

namespace Root\api\components\settings;

/**
 * Class Settings
 *
 * @package Root\api
 */
class Settings
{
    /**
     * @var StorageSettingInterface
     */
    private $storage;

    function __construct(StorageSettingInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if ($this->storage->has($name)) {
            return $this->storage->get($name);
        }
        throw new \Exception('Wrong setting');
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->storage->set($name);
    }
}