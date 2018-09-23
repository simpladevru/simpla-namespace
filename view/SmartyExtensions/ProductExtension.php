<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 20:00
 */

namespace Root\view\SmartyExtensions;

use Root\api\Simpla;

use Root\api\components\design\smarty\BaseExtension;
use Root\api\components\design\smarty\SmartyExtensionInterface;

/**
 * Class ProductExtension
 * @package Root\view\SmartyExtensions
 */
class ProductExtension extends BaseExtension implements SmartyExtensionInterface
{
    public function register()
    {
        // Настраиваем плагины для смарти
        $this->smarty->registerPlugin("function", "get_browsed_products",		array($this, 'get_browsed_products'));
        $this->smarty->registerPlugin("function", "get_featured_products",		array($this, 'get_featured_products_plugin'));
        $this->smarty->registerPlugin("function", "get_new_products",			array($this, 'get_new_products_plugin'));
        $this->smarty->registerPlugin("function", "get_discounted_products",	array($this, 'get_discounted_products_plugin'));
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