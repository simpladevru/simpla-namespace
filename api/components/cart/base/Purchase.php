<?php

namespace Api\components\cart\base;

use stdClass;

class Purchase
{
    private stdClass $product;
    private stdClass $variant;
    private int      $amount;

    /**
     * @param stdClass $product
     * @param stdClass $variant
     * @param int $amount
     */
    public function __construct(stdClass $product, stdClass $variant, int $amount)
    {
        $this->product = $product;
        $this->variant = $variant;
        $this->amount  = $amount;
    }

    /**
     * @return int
     */
    public function get_id(): int
    {
        return $this->variant->id;
    }

    /**
     * @return stdClass
     */
    public function get_product(): stdClass
    {
        return $this->product;
    }

    /**
     * @return stdClass
     */
    public function get_variant(): stdClass
    {
        return $this->variant;
    }

    /**
     * @return int
     */
    public function get_amount(): int
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function get_price(): int
    {
        return $this->variant->price;
    }

    /**
     * @return int
     */
    public function get_cost(): int
    {
        return $this->amount * $this->variant->price;
    }

    /**
     * @param int $amount
     */
    public function update_amount(int $amount)
    {
        $this->amount = min($amount, $this->variant->stock);
    }

    /**
     * @param int $amount
     */
    public function add_amount(int $amount)
    {
        $this->update_amount($amount + $this->amount);
    }
}