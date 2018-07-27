<?php

namespace Root\api;

use Root\api\components\session\Session;

use Root\api\components\settings\Settings;
use Root\api\components\settings\SettingsDbStorage;
use Root\api\components\settings\StorageSettingInterface;

use Root\api\components\cart\base\Cart;
use Root\api\components\cart\base\CartStorageInterface;
use Root\api\components\cart\base\CartSessionStorage;

/**
 * Class Simpla
 * @package Root\api
 *
 * @property Container $container
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
        $this->register_storage();
    }

    public function set_container()
    {
        static::$container = new Container();
    }

    private function register_services()
    {
        foreach($this->bootrstrap() as $abstract => $class) {
            static::$container->singleton($class);
            static::$container->set_alias($class, $abstract);
        }
    }

    private function register_storage()
    {
        static::$container->singleton(
            StorageSettingInterface::class, SettingsDbStorage::class
        );

        static::$container->singleton(
            CartStorageInterface::class, CartSessionStorage::class
        );
    }

    /**
     * @return array
     */
    private function bootrstrap()
    {
        return [
            'config'     => Config::class,
            'settings'   => Settings::class,

            'db'         => Database::class,

            'session'    => Session::class,
            'request'    => Request::class,

            'cart'       => Cart::class,

            'design'     => Design::class,
            'products'   => Products::class,
            'variants'   => Variants::class,
            'categories' => Categories::class,
            'brands'     => Brands::class,
            'features'   => Features::class,
            'money'      => Money::class,
            'pages'      => Pages::class,
            'blog'       => Blog::class,
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