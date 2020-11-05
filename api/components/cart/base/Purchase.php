<?php

namespace Api\components\cart\base;

use Api\entities\shop\catalog\Product;
use Api\entities\shop\catalog\Variant;

class Purchase
{
    private Product $product;
    private Variant $variant;
    private int      $amount;

    /**
     * @param Variant $variant
     * @param int $amount
     */
    public function __construct(Variant $variant, int $amount)
    {
        $this->product = $variant->product;
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
     * @return Product
     */
    public function get_product(): Product
    {
        return $this->product;
    }

    /**
     * @return Variant
     */
    public function get_variant(): Variant
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
        return $this->amount * $this->get_price();
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