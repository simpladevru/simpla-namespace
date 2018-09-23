<?PHP

namespace Root\view;

use Root\api\components\design\smarty\AppExtension;
use Root\api\components\design\smarty\ShopExtension;
use Root\api\components\design\smarty\RegisterSmartyExtension;
use Root\api\Simpla;

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simp.la
 * @author 		Denis Pikusov
 *
 * Базовый класс для всех View
 *
 */

abstract class View
{
    public $currency;
    public $currencies;
    public $user;
    public $group;
    public $page;

    public $pages;
    public $users;
    public $request;
    public $design;
    public $config;
    public $settings;

    private static $view_instance;

    public function __construct()
    {
        if(self::$view_instance)
        {
            $this->currency    = &self::$view_instance->currency;
            $this->currencies  = &self::$view_instance->currencies;
            $this->user        = &self::$view_instance->user;
            $this->group       = &self::$view_instance->group;
            $this->page        = &self::$view_instance->page;

            $this->pages       = &self::$view_instance->pages;
            $this->users       = &self::$view_instance->users;
            $this->request     = &self::$view_instance->request;
            $this->design      = &self::$view_instance->design;
            $this->config      = &self::$view_instance->config;
            $this->settings    = &self::$view_instance->settings;
        }
        else
        {
            self::$view_instance = $this;

            $this->pages    = Simpla::$container->pages;
            $this->users    = Simpla::$container->users;
            $this->request  = Simpla::$container->request;
            $this->design   = Simpla::$container->design;
            $this->config   = Simpla::$container->config;
            $this->settings = Simpla::$container->settings;

            $this->currencies = Simpla()->money->get_currencies(array('enabled'=>1));

            if($currency_id = $this->request->get('currency_id', 'integer')) {
                $_SESSION['currency_id'] = $currency_id;
                header("Location: ".$this->request->url(array('currency_id'=>null)));
            }

            if(isset($_SESSION['currency_id'])) {
                $this->currency = Simpla::$container->money->get_currency($_SESSION['currency_id']);
            }
            else {
                $this->currency = reset($this->currencies);
            }

            if(isset($_SESSION['user_id'])) {
                $u = $this->users->get_user(intval($_SESSION['user_id']));
                if($u && $u->enabled) {
                    $this->user = $u;
                    $this->group = $this->users->get_group($this->user->group_id);

                }
            }

            $subdir = substr(dirname(dirname(__FILE__)), strlen($_SERVER['DOCUMENT_ROOT']));
            $page_url = trim(substr($_SERVER['REQUEST_URI'], strlen($subdir)),"/");
            if(strpos($page_url, '?') !== false)
                $page_url = substr($page_url, 0, strpos($page_url, '?'));
            $this->page = $this->pages->get_page((string)$page_url);
            $this->design->assign('page', $this->page);

            // Передаем в дизайн то, что может понадобиться в нем
            $this->design->assign('currencies',	$this->currencies);
            $this->design->assign('currency',	$this->currency);
            $this->design->assign('user',       $this->user);
            $this->design->assign('group',      $this->group);

            $this->design->assign('config',		$this->config);
            $this->design->assign('settings',	$this->settings);

            // Содержимое корзины
            Simpla::$container->design->assign('cart', Simpla::$container->cart->get_cart());

            // Категории товаров
            Simpla::$container->design->assign('categories', Simpla::$container->categories->get_categories_tree());

            // Страницы
            $pages = Simpla::$container->pages->get_pages(array('visible'=>1));
            Simpla::$container->design->assign('pages', $pages);
        }
    }

}
