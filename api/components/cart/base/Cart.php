<?php

namespace Root\api\components\cart\base;

use Api\components\cart\base\Purchase;
use Api\entities\shop\catalog\Variant;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Root\api\DatabaseIlluminate;

class Cart
{
    private CartStorageInterface $storage;
    private ?Collection          $purchases = null;

    /**
     * @param CartStorageInterface $storage
     * @param DatabaseIlluminate $dbl
     */
    public function __construct(CartStorageInterface $storage, DatabaseIlluminate $dbl)
    {
        $this->storage = $storage;
    }

    /**
     * @return float|int
     */
    public function get_total_price()
    {
        return $this->load_items()->sum(fn(Purchase $purchase) => $purchase->get_cost());
    }

    /**
     * @return float|int
     */
    public function get_total_products()
    {
        return $this->load_items()->sum(fn(Purchase $purchase) => $purchase->get_amount());
    }

    /**
     * @param int $variant_id
     * @return Purchase|null
     */
    public function get_purchase(int $variant_id): ?Purchase
    {
        return $this->load_items()->get($variant_id, null);
    }

    /**
     * @param int $variant_id
     * @return bool
     */
    public function has_purchase(int $variant_id): bool
    {
        return $this->load_items()->has($variant_id);
    }

    /**
     * @return bool
     */
    public function has_purchases(): bool
    {
        return $this->load_items()->isNotEmpty();
    }

    /**
     * @return Collection
     */
    public function get_purchases(): Collection
    {
        return $this->load_items();
    }

    /**
     * @param Purchase $purchase
     */
    public function add_purchase(Purchase $purchase): void
    {
        if ($this->has_purchase($purchase->get_id())) {
            $this->get_purchase($purchase->get_id())->add_amount($purchase->get_amount());
        } else {
            $this->purchases->put($purchase->get_id(), $purchase);
        }
    }

    /**
     * @param int $variant_id
     * @param int $amount
     */
    public function update_purchase(int $variant_id, int $amount = 1): void
    {
        $this->get_purchase($variant_id)->change_amount($amount);
    }

    /**
     * @param int $variant_id
     */
    public function delete_purchase(int $variant_id): void
    {
        $this->get_purchases()->forget($variant_id);
    }

    public function empty_cart(): void
    {
        $this->purchases = null;
    }

    public function save(): void
    {
        $this->storage->save_items($this->purchases ? $this->purchases->toArray() : []);
    }

    /**
     * @return Collection
     */
    private function load_items(): Collection
    {
        $this->purchases = $this->purchases ?? new Collection();

        if ($this->purchases->isEmpty() && $this->storage->has_items()) {
            $storage_items = $this->storage->get_items();
            $variants      = $this->get_variants_by_ids($this->storage->get_item_ids());

            foreach ($storage_items as $storage_item) {
                if ($variant = $variants->get($storage_item['variant_id'])->first()) {
                    $this->purchases->put($variant->id, new Purchase($variant, (int) $storage_item['amount']));
                }
            }
        }

        return $this->purchases;
    }

    /**
     * @param array $ids
     * @return EloquentCollection
     */
    private function get_variants_by_ids(array $ids): EloquentCollection
    {
        return Variant::query()->whereIn('id', $ids)->with(['product', 'product.images'])
            ->get()->groupBy('id');
    }
}
