<?php

namespace Root\api;

/**
 * Class Container
 * @package Root\api
 *
 * @property Config $config
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
 *
 */
class Container
{
    private $classes = [];

    private $objects = array();

    public function __construct($classes)
    {
        $this->classes = $classes;
    }

    public function __get($name)
    {
        if(isset($this->objects[$name])) {
            return($this->objects[$name]);
        }

        if(!array_key_exists($name, $this->classes)) {
            return null;
        }

        $this->objects[$name] = new $this->classes[$name]();

        return $this->objects[$name];
    }
}