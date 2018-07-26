<?php

namespace Root\api;

use Root\api\components\settings\Settings;
use Root\api\components\settings\SettingsDbStorage;

/**
 * Class Simpla
 * @package Root\api
 */
class Simpla
{
    public static $app;

    public function __construct()
    {
        static::$app = new Container($this->bootrstrap());

        foreach ($this->alias() as $abstract => $alias) {
            static::$app->set_alias($alias, $abstract);
        }
    }

    /**
     * @return array
     */
    private function alias()
    {
        return [
            Database::class => 'db',
            Request::class => 'request'
        ];
    }

    /**
     * @return array
     */
    private function bootrstrap()
    {
        return [
            'config'     => Config::class,
            'request'    => Request::class,
            'db'         => Database::class,

            'settings'   => function($container) {
                $storage = new SettingsDbStorage($container->db);
                return new Settings($storage);
            },

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