<?php

namespace Root\api;

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

class Payment
{
	public function get_payment_methods($filter = array())
	{	
		$delivery_filter = '';
		if(!empty($filter['delivery_id'])) {
			$delivery_filter = db()->placehold('AND id in (SELECT payment_method_id FROM __delivery_payment dp WHERE dp.delivery_id=?)', intval($filter['delivery_id']));
        }
		
		$enabled_filter = '';
 		if(!empty($filter['enabled'])) {
			$enabled_filter = db()->placehold('AND enabled=?', intval($filter['enabled']));
        }

		$query = "SELECT * FROM __payment_methods WHERE 1 $delivery_filter $enabled_filter ORDER BY position";
	
		db()->query($query);
		return db()->results();
	}
	
	function get_payment_method($id)
	{
		$query = db()->placehold("SELECT * FROM __payment_methods WHERE id=? LIMIT 1", intval($id));
		db()->query($query);
		$payment_method = db()->result();
  		return $payment_method;
	}
	
	function get_payment_settings($method_id)
	{
		$query = db()->placehold("SELECT settings FROM __payment_methods WHERE id=? LIMIT 1", intval($method_id));
		db()->query($query);
		$settings = db()->result('settings');
 
		$settings = unserialize($settings);
		return $settings;
	}
	
	function get_payment_modules()
	{
		$modules_dir = $this->config->root_dir.'payment/';
		
		$modules = array();
		$handler = opendir($modules_dir);		
		while ($dir = readdir($handler))
		{
			$dir = preg_replace("/[^A-Za-z0-9]+/", "", $dir);
			if (!empty($dir) && $dir != "." && $dir != ".." && is_dir($modules_dir.$dir))
			{
				
				if(is_readable($modules_dir.$dir.'/settings.xml') && $xml = simplexml_load_file($modules_dir.$dir.'/settings.xml'))
				{
					$module = new \stdClass;
					
					$module->name = (string)$xml->name;
					$module->settings = array();
	
					foreach($xml->settings as $setting)
					{
						$module->settings[(string)$setting->variable] = new \stdClass;
						$module->settings[(string)$setting->variable]->name = (string)$setting->name;
						$module->settings[(string)$setting->variable]->variable = (string)$setting->variable;
					 	$module->settings[(string)$setting->variable]->variable_options = array();
					 	foreach($setting->options as $option)
					 	{
					 		$module->settings[(string)$setting->variable]->options[(string)$option->value] = new \stdClass;
					 		$module->settings[(string)$setting->variable]->options[(string)$option->value]->name = (string)$option->name;
					 		$module->settings[(string)$setting->variable]->options[(string)$option->value]->value = (string)$option->value;
					 	}
					}
					$modules[$dir] = $module;
				}

			}
		}
    	closedir($handler);
    	return $modules;

	}
	
	public function get_payment_deliveries($id)
	{
		$query = db()->placehold("SELECT delivery_id FROM __delivery_payment WHERE payment_method_id=?", intval($id));
		db()->query($query);
		return db()->results('delivery_id');
	}		
	
	public function update_payment_method($id, $payment_method)
	{
		$query = db()->placehold("UPDATE __payment_methods SET ?% WHERE id in(?@)", $payment_method, (array)$id);
		db()->query($query);
		return $id;
	}
	
	public function update_payment_settings($method_id, $settings)
	{
		if(!is_string($settings)) {
			$settings = serialize($settings);
		}
		$query = db()->placehold("UPDATE __payment_methods SET settings=? WHERE id in(?@) LIMIT 1", $settings, (array)$method_id);
		db()->query($query);
		return $method_id;
	}
	
	public function update_payment_deliveries($id, $deliveries_ids)
	{
		$query = db()->placehold("DELETE FROM __delivery_payment WHERE payment_method_id=?", intval($id));
		db()->query($query);
		if(is_array($deliveries_ids))
		foreach($deliveries_ids as $d_id) {
			db()->query("INSERT INTO __delivery_payment SET payment_method_id=?, delivery_id=?", $id, $d_id);
        }
	}		
	
	public function add_payment_method($payment_method)
	{	
		$query = db()->placehold('INSERT INTO __payment_methods SET ?%', $payment_method);

		if(!db()->query($query)) {
			return false;
        }

		$id = db()->insert_id();
		db()->query("UPDATE __payment_methods SET position=id WHERE id=?", $id);	
		return $id;
	}

	public function delete_payment_method($id)
	{
		if(!empty($id)) {
			$query = db()->placehold("DELETE FROM __payment_methods WHERE id=? LIMIT 1", intval($id));
			db()->query($query);
		}
	}	

	
}
