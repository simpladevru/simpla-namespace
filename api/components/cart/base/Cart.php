<?php

namespace Root\api\components\cart\base;

use Api\components\cart\base\Purchase;
use DomainException;
use Illuminate\Support\Collection;
use Root\api\Coupons;
use Root\api\Products;
use Root\api\Variants;
use stdClass;

class Cart
{
    private CartStorageInterface $storage;
    private Products $products;
    private Variants $variants;
    private Coupons $coupons;

    private ?Collection $purchases = null;
    private ?stdClass $coupon = null;

    /**
     * @param CartStorageInterface $storage
     * @param Products $products
     * @param Variants $variants
     * @param Coupons $coupons
     */
    public function __construct(
        CartStorageInterface $storage,
        Products $products,
        Variants $variants,
        Coupons $coupons
    )
    {
        $this->storage  = $storage;
        $this->products = $products;
        $this->variants = $variants;
        $this->coupons  = $coupons;
    }

    /**
     * @return float|int
     */
    public function get_total_price()
    {
        return$this->get_purchases()->sum(fn (Purchase $purchase) => $purchase->get_cost());
    }

    /**
     * @return float|int
     */
    public function get_total_products()
    {
        return$this->get_purchases()->sum(fn (Purchase $purchase) => $purchase->get_amount());
    }

    /**
     * @return stdClass|null
     */
    public function get_coupon(): ?stdClass
    {
        if(!$this->coupon && $this->storage->has_coupon_code() ) {
            $this->coupon = $this->coupons->get_coupon($this->storage->get_coupon_code());
        }

        return $this->coupon;
    }

    /**
     * @return int
     */
    public function get_discount()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function get_coupon_discount()
    {
        return 0;
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
            $variant = $this->get_variant_by_id((int) $variant_id);
            $product = $this->get_product_by_id((int) $variant->product_id);
            $this->purchases->put($variant_id, new Purchase($product, $variant, $amount));
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
     * @param string $coupon_code
     */
    public function apply_coupon(string $coupon_code): void
    {
        $coupon = $this->coupons->get_coupon($coupon_code);

        if ($coupon && $coupon->valid) {
            $this->storage->save_coupon_code($coupon->code);
        } else {
            $this->storage->remove_coupon_code();
        }
    }

    /**
     * @return Collection
     */
    private function load_items(): Collection
    {
        $this->purchases = $this->purchases ?? new Collection();

        if ($this->purchases->isEmpty() && $this->storage->has_items()) {
            $storage_items = $this->storage->get_items();

            $variants = new Collection(array_column($this->variants->get_variants(['id' => array_keys($storage_items)]), null, 'id'));
            $products = new Collection(array_column($this->products->get_products(['id' => array_column($variants, 'product_id')]), null, 'id'));
            $images   = (new Collection($this->products->get_images(['product_id' => array_column($products, 'id')])))->groupBy('product_id');

            foreach (array_intersect_key($storage_items, $variants) as $storage_item) {
                $variant         = $variants->get($storage_item['variant_id']);
                $product         = $products->get($variant->product_id);
                $product->images = $images->get($product->id, []);

                $this->purchases->put($variant->id, new Purchase($product, $variant, $storage_item['amount']));
            }
        }

        return $this->purchases;
    }

    /**
     * @param int $product_id
     * @return stdClass
     */
    private function get_product_by_id(int $product_id): stdClass
    {
        /** @var stdClass $product */
        $product = $this->products->get_product($product_id);

        if (!$product) {
            throw new DomainException('product not found');
        }

        return $product;
    }

    /**
     * @param int $variant_id
     * @return stdClass
     */
    private function get_variant_by_id(int $variant_id): stdClass
    {
        /** @var stdClass $variant */
        $variant = $this->variants->get_variant($variant_id);

        if (!$variant) {
            throw new DomainException('variant not found');
        }

        return $variant;
    }
}
