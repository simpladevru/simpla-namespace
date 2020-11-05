<?php

namespace Api\entities\shop\catalog;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property Collection $images
 */
class Product extends Model
{
    protected $table = 's_products';

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'product_id', 'id');
    }
}