<?php

namespace Root\api\components\cart\base;

interface CartStorageInterface
{
    public function has_items();
    public function get_items();
    public function get_item_ids();

    public function save_items(array $items);
    public function clear();

    public function has_coupon_code();
    public function save_coupon_code(string $code);
    public function get_coupon_code();
    public function remove_coupon_code();
}