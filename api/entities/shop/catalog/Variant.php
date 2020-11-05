<?php

namespace Api\entities\shop\catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $stock
 * @property float $price
 * @property Product $product
 */
class Variant extends Model
{
    protected $table  = 's_variants';

    protected $fillable = ['price', 'stock'];

    /**
     * @return int
     */
    public function getStock(): int
    {
        return !is_null($this->stock) ? $this->stock : settings()->max_order_amount;
    }

    /**
     * @param int $amount
     * @return int
     */
    public function getAvailableStockByAmount(int $amount): int
    {
        return min($amount, $this->getStock());
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}