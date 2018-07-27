<?php

namespace Root\api;

use Root\api\components\settings\Settings;
use Root\api\components\settings\SettingsDbStorage;
use Root\api\components\settings\StorageSettingInterface;

/**
 * Class Simpla
 * @package Root\api
 *
 * @property \Illuminate\Container\Container $container
 *
 * * @property Config $config
 * @property Request $request
 * @property Database $db
 * @property Settings $settings
 * @property Design $design
 * @property Products $products
 * @property Variants $variants
 * @property Categories $categories
 * @property Brands $brands
 * @property Features $features
 * @property Money $money
 * @property Pages $pages
 * @property Blog $blog
 * @property Cart $cart
 * @property Image $image
 * @property Delivery $delivery
 * @property Payment $payment
 * @property Orders $orders
 * @property Users $users
 * @property Coupons $coupons
 * @property Comments $comments
 * @property Feedbacks $feedbacks
 * @property Notify $notify
 * @property Managers $managers
 */
class Simpla
{
    public static $container;
    public static $test;

    public function __construct()
    {
        $this->set_container();
        $this->register_services();
        $this->set_settings_storage();
    }

    public function set_container()
    {
        static::$container = new \Illuminate\Container\Container();
    }

    public function register_services()
    {
        foreach($this->bootrstrap() as $abstract => $class) {
            static::$container->singleton($class);
            static::$container->alias($class, $abstract);
        }
    }

    public function set_settings_storage()
    {
        static::$container->singleton(
            StorageSettingInterface::class, SettingsDbStorage::class
        );
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