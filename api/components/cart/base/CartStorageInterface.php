<?php

namespace Root\api\components\cart\base;

interface CartStorageInterface
{
    public function has_items();
    public function get_items();

    public function has_item($key);
    public function get($key);
    public function update_item($key, $amount);
    public function delete_item($key);

    public function clear();

    public function has_coupon_code();
    public function set_coupon_code($code);
    public function get_coupon_code();
    public function delete_coupon_code();
}