<?php

namespace Root\api\components\cart\base;

use Root\api\components\session\Session;

class CartSessionStorage implements CartStorageInterface
{
    const CART_KEY   = 'shopping_cart';
    const COUPON_KEY = 'coupon_code';

    private Session $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return bool
     */
    public function has_items(): bool
    {
        return !empty($_SESSION[static::CART_KEY]);
    }

    /**
     * @return array
     */
    public function get_items(): array
    {
        if (!empty($_SESSION[static::CART_KEY])) {
            return $_SESSION[static::CART_KEY];
        }

        return [];
    }

    /**
     * @return array
     */
    public function get_item_ids(): array
    {
        return array_keys($this->get_items());
    }

    /**
     * @param array $items
     */
    public function save_items(array $items): void
    {
        $_SESSION[static::CART_KEY] = $items;
    }

    public function clear(): void
    {
        unset($_SESSION[static::CART_KEY]);
        $this->remove_coupon_code();
    }

    /**
     * @return bool
     */
    public function has_coupon_code(): bool
    {
        return isset($_SESSION[static::COUPON_KEY]);
    }

    /**
     * @param string $code
     */
    public function save_coupon_code(string $code): void
    {
        $_SESSION[static::COUPON_KEY] = $code;
    }

    /**
     * @return string|null
     */
    public function get_coupon_code(): ?string
    {
        return $this->has_coupon_code() ? $_SESSION[static::COUPON_KEY] : null;
    }

    public function remove_coupon_code(): void
    {
        unset($_SESSION[static::COUPON_KEY]);
    }
}