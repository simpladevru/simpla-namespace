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
        $this->storage  = $storage;
    }

    /**
     * @return float|int
     */
    public function get_total_price()
    {
        return $this->get_purchases()->sum(fn(Purchase $purchase) => $purchase->get_cost());
    }

    /**
     * @return float|int
     */
    public function get_total_products()
    {
        return $this->get_purchases()->sum(fn(Purchase $purchase) => $purchase->get_amount());
    }

    /**
     * @param int $variant_id
     * @return Purchase|null
     */
    public function get_purchase(int $variant_id): ?Purchase
    {
        return $this->get_purchases()->get($variant_id, null);
    }

    /**
     * @param int $variant_id
     * @return bool
     */
    public function has_purchase(int $variant_id): bool
    {
        return $this->get_purchases()->has($variant_id);
    }

    /**
     * @return bool
     */
    public function has_purchases(): bool
    {
        return $this->get_purchases()->isNotEmpty();
    }

    /**
     * @return Collection
     */
    public function get_purchases(): Collection
    {
        return $this->load_items();
    }

    /**
     * @param int $variant_id
     * @param int $amount
     */
    public function add_purchase(int $variant_id, int $amount = 1): void
    {
        $amount = max(1, $amount);

        if ($this->has_purchase($variant_id)) {
            $this->get_purchase($variant_id)->add_amount($amount);
        } else {
            $this->purchases->put($variant_id, new Purchase(Variant::findOrFail($variant_id), (int) $amount));
        }
    }

    /**
     * @param int $variant_id
     * @param int $amount
     */
    public function update_purchase(int $variant_id, int $amount = 1): void
    {
        $this->get_purchase($variant_id)->update_amount(max(1, $amount));
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
        $this->storage->save_items(
            $this->get_purchases()->map(function (Purchase $purchase) {
                return [
                    'variant_id' => $purchase->get_id(),
                    'amount'     => $purchase->get_amount(),
                ];
            })->all()
        );
    }

    /**
     * @return Collection
     */
    private function load_items(): Collection
    {
        $this->purchases = $this->purchases ?? new Collection();

        if ($this->purchases->isEmpty() && $this->storage->has_items()) {
            $storage_items = $this->storage->get_items();
            $variants      = $this->get_variants_by_ids(array_keys($this->storage->get_items()));

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
