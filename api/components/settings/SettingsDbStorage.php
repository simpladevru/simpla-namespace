<?php

namespace Root\api\components\settings;

use Root\api\Database;

/**
 * Class SettingsDbStorage
 *
 * @package Root\api\components\settings
 */
class SettingsDbStorage implements StorageSettingInterface
{
    private $vars = [];

    private $db;

    function __construct(Database $db)
    {
        $this->db = $db;
        $this->init();
    }

    public static function table()
    {
        return '__settings';
    }

    private function init()
    {
        $query = db()->build()
            ->select(['name', 'value'])
            ->from(static::table());

        foreach ($query->all() as $result) {
            if (!($this->vars[$result->name] = @unserialize($result->value))) {
                $this->vars[$result->name] = $result->value;
            }
        }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->vars[$name] = $value;

        if (is_array($value)) {
            $value = serialize($value);
        } else {
            $value = (string) $value;
        }

        $this->db->query('SELECT count(*) as count FROM __settings WHERE name=?', $name);
        if ($this->db->result('count') > 0) {
            $this->db->query('UPDATE __settings SET value=? WHERE name=?', $value, $name);
        } else {
            $this->db->query('INSERT INTO __settings SET value=?, name=?', $value, $name);
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return !empty($this->vars[$name]);
    }
}