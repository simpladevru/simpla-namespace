<?php

namespace Root\api\components\settings;

use Root\api\Database;

/**
 * Class SittingsDbStorage
 * @package Root\api\components\settings
 */
class SettingsDbStorage implements StorageSettingInterface
{
    private $vars = array();
    private $db;

    function __construct(Database $db)
    {
        $this->db = $db;
        $this->init();
    }

    private function init()
    {
        $this->db->query('SELECT name, value FROM __settings');
        foreach($this->db->results() as $result) {
            if(!($this->vars[$result->name] = @unserialize($result->value))) {
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
        if(isset($this->vars[$name])) {
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

        if(is_array($value)) {
            $value = serialize($value);
        }
        else {
            $value = (string) $value;
        }

        $this->db->query('SELECT count(*) as count FROM __settings WHERE name=?', $name);
        if($this->db->result('count')>0) {
            $this->db->query('UPDATE __settings SET value=? WHERE name=?', $value, $name);
        }
        else {
            $this->db->query('INSERT INTO __settings SET value=?, name=?', $value, $name);
        }
    }
}