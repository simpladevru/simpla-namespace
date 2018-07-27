<?php

namespace Root\api\components\cart\base;

use Root\api\components\session\Session;

/**
 * Class CartSessionStorage
 * @package api\components\cart
 */
class CartSessionStorage implements CartStorageInterface
{
    const CART_KEY = 'shopping_cart';
    const COUPON_KEY = 'coupon_code';

    public function __construct(Session $session)
    {

    }

    public function has_items()
    {
        return !empty( $_SESSION[static::CART_KEY] );
    }

    public function get_items()
    {
        $items = [];
        if ( !empty($_SESSION[static::CART_KEY]) ) {
            foreach($_SESSION[static::CART_KEY] as $id => $amount) {
                $items[$id] = ['amount' => $amount];
            }
        }
        return $items;
    }

    public function has_item($key)
    {
        return isset($_SESSION[static::CART_KEY][$key]);
    }

    public function get($key)
    {
        return $this->has_item($key)
            ? ['variant_id' => $key, 'amount' => $_SESSION[static::CART_KEY][$key]]
            : false;
    }

    public function update_item($key, $amount)
    {
        $_SESSION[static::CART_KEY][$key] = $amount;
    }

    public function delete_item($key)
    {
        unset($_SESSION[static::CART_KEY][$key]);
    }

    public function clear()
    {
        unset($_SESSION[static::CART_KEY]);
        $this->delete_coupon_code();
    }

    public function has_coupon_code()
    {
        return !isset($_SESSION[static::COUPON_KEY]);
    }

    public function set_coupon_code($code)
    {
        $_SESSION[static::COUPON_KEY] = $code;
    }

    public function get_coupon_code()
    {
        return $this->has_coupon_code()
            ? $_SESSION[static::COUPON_KEY]
            : false;
    }

    public function delete_coupon_code()
    {
        unset($_SESSION[static::COUPON_KEY]);
    }
}