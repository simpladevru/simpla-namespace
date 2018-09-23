<?php

namespace Root\api;
use Root\api\components\design\smarty\AppExtension;
use Root\api\components\design\smarty\RegisterSmartyExtension;
use Root\helpers\Debug;
use Smarty;

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */
class Design
{
	public $smarty;

	private $config;
    private $settings;

	public function __construct()
	{
		$this->settings = Simpla::$container->settings;
		$this->config = Simpla::$container->config;

		// Создаем и настраиваем Смарти
		$this->smarty = new Smarty();

		$this->smarty->compile_check = $this->config->smarty_compile_check;
		$this->smarty->caching = $this->config->smarty_caching;
		$this->smarty->cache_lifetime = $this->config->smarty_cache_lifetime;
		$this->smarty->debugging = $this->config->smarty_debugging;
		$this->smarty->error_reporting = E_ALL & ~E_NOTICE;

		// Берем тему из настроек
		$theme = $this->settings->theme;

		$this->smarty->compile_dir = $this->config->root_dir.'/compiled/'.$theme;
		$this->smarty->template_dir = $this->config->root_dir.'/design/'.$theme.'/html';		

		// Создаем папку для скомпилированных шаблонов текущей темы
		if(!is_dir($this->smarty->compile_dir)) {
			mkdir($this->smarty->compile_dir, 0777);
        }
						
		$this->smarty->cache_dir = 'cache';

		if($this->config->smarty_html_minify) {
			$this->smarty->loadFilter('output', 'trimwhitespace');
        }
	}
	
	public function assign($var, $value)
	{
		return $this->smarty->assign($var, $value);
	}

	public function fetch($template)
	{
		// Передаем в дизайн то, что может понадобиться в нем
		$this->assign('config',		$this->config);
		$this->assign('settings',	$this->settings);
		return $this->smarty->fetch($template);
	}
	
	public function set_templates_dir($dir)
	{
		$this->smarty->template_dir = $dir;			
	}

	public function set_compiled_dir($dir)
	{
		$this->smarty->compile_dir = $dir;
	}
	
	public function get_var($name)
	{
		return $this->smarty->getTemplateVars($name);
	}
	
	public function clear_cache()
	{
		$this->smarty->clearAllCache();	
	}
}