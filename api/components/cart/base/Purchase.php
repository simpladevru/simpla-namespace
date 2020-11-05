<?php

namespace Api\components\cart\base;

use Api\entities\shop\catalog\Image;
use Api\entities\shop\catalog\Product;
use Api\entities\shop\catalog\Variant;
use Illuminate\Contracts\Support\Arrayable;

class Purchase implements Arrayable
{
    private Variant $variant;
    private int      $amount;

    /**
     * @param Variant $variant
     * @param int $amount
     */
    public function __construct(Variant $variant, int $amount)
    {
        $this->variant = $variant;
        $this->update_amount($amount);
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
        return $this->variant->product;
    }

    /**
     * @return Variant
     */
    public function get_variant(): Variant
    {
        return $this->variant;
    }

    /**
     * @return Image|null
     */
    public function get_image(): ?Image
    {
        return $this->variant->product->images->first();
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
        return $this->get_amount() * $this->get_price();
    }

    /**
     * @param int $amount
     */
    public function update_amount(int $amount)
    {
        $this->amount = $this->variant->getAvailableStockByAmount(max(1, $amount));
    }

    /**
     * @param int $amount
     */
    public function add_amount(int $amount)
    {
        $this->update_amount($amount + $this->amount);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['variant_id' => $this->get_id(), 'amount' => $this->get_amount()];
    }
}