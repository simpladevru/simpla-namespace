<?php

namespace Root\api;

use Root\api\components\settings\StorageSettingInterface;

/**
 * Class Settings
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
     */
	public function __get($name)
	{
        return $this->storage->get($name);
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