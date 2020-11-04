<?php

namespace Root\api;

use Root\api\components\session\Session;

use Root\api\components\settings\Settings;
use Root\api\components\settings\SettingsDbStorage;
use Root\api\components\settings\StorageSettingInterface;

use Root\api\components\cart\base\Cart;
use Root\api\components\cart\base\CartStorageInterface;
use Root\api\components\cart\base\CartSessionStorage;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Simpla
 * @package Root\api
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
 * @property Filesystem $filesystem
 */
class Simpla extends Container
{
    /** @var Container */
    public static $container;

    /** @var self */
    private static $instance;

    public function __construct()
    {
        $this->set_container();
        $this->register_services();
        $this->register_storage();

        static::$instance = $this;
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    public function set_container()
    {
        static::$container = $this;
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
            'filesystem' => Filesystem::class,

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