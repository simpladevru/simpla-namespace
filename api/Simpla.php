<?php

namespace Root\api;

/**
 * Основной класс Simpla для доступа к API Simpla
 *
 * @copyright 	2014 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 * @property Container $app
 *
 */
class Simpla
{
    public static $app;

    public function __construct()
    {
        static::$app = new Container($this->bootrstrap());
    }

    /**
     * @return array
     */
    private function bootrstrap()
    {
        return [
            'config'     => Config::class,
            'request'    => Request::class,
            'db'         => function($container) {
                return new Database($container->config);
            },
            'settings'   => Settings::class,
            'design'     => Design::class,
            'products'   => Products::class,
            'variants'   => Variants::class,
            'categories' => Categories::class,
            'brands'     => Brands::class,
            'features'   => Features::class,
            'money'      => Money::class,
            'pages'      => Pages::class,
            'blog'       => Blog::class,
            'cart'       => Cart::class,
            'image'      => Image::class,
            'delivery'   => Delivery::class,
            'payment'    => Payment::class,
            'orders'     => Orders::class,
            'users'      => Users::class,
            'coupons'    => Coupons::class,
            'comments'   => Comments::class,
            'feedbacks'  => Feedbacks::class,
            'notify'     => Notify::class,
            'managers'   => Managers::class
        ];
    }
}