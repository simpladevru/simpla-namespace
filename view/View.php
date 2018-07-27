<?PHP

namespace Root\view;
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

    protected $pages;
    protected $users;
    protected $request;
    protected $design;
    protected $config;
    protected $settings;

    public function __construct()
    {
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

        // Настраиваем плагины для смарти
        $this->design->smarty->registerPlugin("function", "get_posts",					array($this, 'get_posts_plugin'));
        $this->design->smarty->registerPlugin("function", "get_brands",					array($this, 'get_brands_plugin'));
        $this->design->smarty->registerPlugin("function", "get_browsed_products",		array($this, 'get_browsed_products'));
        $this->design->smarty->registerPlugin("function", "get_featured_products",		array($this, 'get_featured_products_plugin'));
        $this->design->smarty->registerPlugin("function", "get_new_products",			array($this, 'get_new_products_plugin'));
        $this->design->smarty->registerPlugin("function", "get_discounted_products",	array($this, 'get_discounted_products_plugin'));
    }

    /**
     *
     * Плагины для смарти
     *
     */
    public function get_posts_plugin($params, $smarty)
    {
        if(!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        if(!empty($params['var'])) {
            $smarty->assign($params['var'], Simpla::$container->blog->get_posts($params));
        }
    }

    public function get_brands_plugin($params, $smarty)
    {
        if(!empty($params['var'])) {
            $smarty->assign($params['var'], Simpla::$container->brands->get_brands($params));
        }
    }

    public function get_browsed_products($params, $smarty)
    {
        if(!empty($_COOKIE['browsed_products']))
        {
            $browsed_products_ids = explode(',', $_COOKIE['browsed_products']);
            $browsed_products_ids = array_reverse($browsed_products_ids);

            if(isset($params['limit'])) {
                $browsed_products_ids = array_slice($browsed_products_ids, 0, $params['limit']);
            }

            $products = array();
            foreach(Simpla::$container->products->get_products(array('id'=>$browsed_products_ids, 'visible'=>1)) as $p) {
                $products[$p->id] = $p;
            }

            $browsed_products_images = Simpla::$container->products->get_images(array('product_id'=>$browsed_products_ids));
            foreach($browsed_products_images as $browsed_product_image) {
                if(isset($products[$browsed_product_image->product_id])) {
                    $products[$browsed_product_image->product_id]->images[] = $browsed_product_image;
                }
            }

            foreach($browsed_products_ids as $id) {
                if(isset($products[$id])) {
                    if(isset($products[$id]->images[0])) {
                        $products[$id]->image = $products[$id]->images[0];
                    }
                    $result[] = $products[$id];
                }
            }
            $smarty->assign($params['var'], $result);
        }
    }


    public function get_featured_products_plugin($params, $smarty)
    {
        if(!isset($params['visible'])) {
            $params['visible'] = 1;
        }
        $params['featured'] = 1;
        if(!empty($params['var']))
        {
            foreach(Simpla::$container->products->get_products($params) as $p)
                $products[$p->id] = $p;

            if(!empty($products))
            {
                // id выбраных товаров
                $products_ids = array_keys($products);

                // Выбираем варианты товаров
                $variants = Simpla::$container->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));

                // Для каждого варианта
                foreach($variants as &$variant)
                {
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = Simpla::$container->products->get_images(array('product_id'=>$products_ids));
                foreach($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach($products as &$product)
                {
                    if(isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if(isset($product->images[0]))
                        $product->image = $product->images[0];
                }
            }

            $smarty->assign($params['var'], $products);

        }
    }


    public function get_new_products_plugin($params, $smarty)
    {
        if(!isset($params['visible']))
            $params['visible'] = 1;
        if(!isset($params['sort']))
            $params['sort'] = 'created';
        if(!empty($params['var']))
        {
            foreach(Simpla::$container->products->get_products($params) as $p)
                $products[$p->id] = $p;

            if(!empty($products))
            {
                // id выбраных товаров
                $products_ids = array_keys($products);

                // Выбираем варианты товаров
                $variants = Simpla::$container->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));

                // Для каждого варианта
                foreach($variants as &$variant)
                {
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = Simpla::$container->products->get_images(array('product_id'=>$products_ids));
                foreach($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach($products as &$product)
                {
                    if(isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if(isset($product->images[0]))
                        $product->image = $product->images[0];
                }
            }

            $smarty->assign($params['var'], $products);

        }
    }


    public function get_discounted_products_plugin($params, $smarty)
    {
        if(!isset($params['visible']))
            $params['visible'] = 1;
        $params['discounted'] = 1;
        if(!empty($params['var']))
        {
            foreach(Simpla::$container->products->get_products($params) as $p)
                $products[$p->id] = $p;

            if(!empty($products))
            {
                // id выбраных товаров
                $products_ids = array_keys($products);

                // Выбираем варианты товаров
                $variants = Simpla::$container->variants->get_variants(array('product_id'=>$products_ids, 'in_stock'=>true));

                // Для каждого варианта
                foreach($variants as &$variant)
                {
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = Simpla::$container->products->get_images(array('product_id'=>$products_ids));
                foreach($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach($products as &$product)
                {
                    if(isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if(isset($product->images[0]))
                        $product->image = $product->images[0];
                }
            }
            $smarty->assign($params['var'], $products);
        }
    }
}
