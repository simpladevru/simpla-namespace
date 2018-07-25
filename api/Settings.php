<?php

namespace Root\api;

/**
 * Управление настройками магазина, хранящимися в базе данных
 * В отличие от класса Config оперирует настройками доступными админу и хранящимися в базе данных.
 *
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

class Settings
{
	private $vars = array();

	function __construct()
	{
		// Выбираем из базы настройки
		db()->query('SELECT name, value FROM __settings');

		// и записываем их в переменную		
		foreach(db()->results() as $result) {
			if(!($this->vars[$result->name] = @unserialize($result->value))) {
				$this->vars[$result->name] = $result->value;
            }
        }
	}
	
	public function __get($name)
	{
		if(isset($this->vars[$name])) {
			return $this->vars[$name];
        } else {
			return null;
        }
	}
	
	public function __set($name, $value)
	{
		$this->vars[$name] = $value;

		if(is_array($value)) {
			$value = serialize($value);
        }
		else {
			$value = (string) $value;
        }
			
		db()->query('SELECT count(*) as count FROM __settings WHERE name=?', $name);
		if(db()->result('count')>0) {
			db()->query('UPDATE __settings SET value=? WHERE name=?', $value, $name);
        }
		else {
			db()->query('INSERT INTO __settings SET value=?, name=?', $value, $name);
        }
	}
}