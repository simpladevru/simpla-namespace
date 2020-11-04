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

    /** @var array|Purchase  */
    private array $purchases = [];
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
        return array_sum(array_map(function (Purchase $purchase) {
            return $purchase->get_cost();
        }, $this->get_purchases()));
    }

    /**
     * @return float|int
     */
    public function get_total_products()
    {
        return array_sum(array_map(function (Purchase $purchase) {
            return $purchase->get_amount();
        }, $this->get_purchases()));
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
     * @return bool
     */
    public function has_purchases(): bool
    {
        return count($this->load_items()) > 0;
    }

    /**
     * @return array
     */
    public function get_purchases(): array
    {
        return $this->load_items();
    }

    /**
     * @param int $variant_id
     * @return Purchase
     */
    public function get_purchase(int $variant_id): Purchase
    {
        if (!$this->has_purchase($variant_id)) {
            throw new DomainException('purchase not found');
        }

        return $this->purchases[$variant_id];
    }

    /**
     * @param int $variant_id
     * @return bool
     */
    public function has_purchase(int $variant_id): bool
    {
        return !empty($this->load_items()[$variant_id]);
    }

    /**
     * @param int $variant_id
     * @param int $amount
     */
    public function add_purchase(int $variant_id, int $amount = 1): void
    {
        $amount = max(1, $amount);

        if ($this->has_purchase($variant_id)) {
            $purchase = $this->get_purchase($variant_id);
            $purchase->add_amount($amount);
        } else {
            $variant = $this->get_variant_by_id((int) $variant_id);
            $product = $this->get_product_by_id((int) $variant->product_id);
            $this->purchases[$variant_id] = new Purchase($product, $variant, $amount);
        }
    }

    /**
     * @param int $variant_id
     * @param int $amount
     */
    public function update_purchase(int $variant_id, int $amount = 1): void
    {
        if ($this->has_purchase($variant_id)) {
            $this->get_purchase($variant_id)->update_amount(max(1, $amount));
        }
    }

    /**
     * @param int $variant_id
     */
    public function delete_purchase(int $variant_id): void
    {
        if ($this->has_purchase($variant_id)) {
            unset($this->purchases[$variant_id]);
        }
    }

    public function empty_cart(): void
    {
        $this->purchases = [];
    }

    public function save(): void
    {
        $this->storage->save_items(array_map(function (Purchase $purchase) {
            return [
                'variant_id' => $purchase->get_id(),
                'amount'     => $purchase->get_amount(),
            ];
        }, $this->get_purchases()));
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

    /**
     * @return array
     */
    private function load_items(): array
    {
        if (!$this->purchases && $this->storage->has_items()) {
            $storage_items = $this->storage->get_items();

            $variants = array_column($this->variants->get_variants(['id' => array_keys($storage_items)]), null, 'id');
            $products = array_column($this->products->get_products(['id' => array_column($variants, 'product_id')]), null, 'id');
            $images   = (new Collection($this->products->get_images(['product_id' => array_column($products, 'id')])))->groupBy('product_id');

            foreach (array_intersect_key($storage_items, $variants) as $storage_item) {
                $variant         = $variants[$storage_item['variant_id']];
                $product         = $products[$variant->product_id];
                $product->images = $images->get($product->id, []);

                $this->purchases[$variant->id] = new Purchase($product, $variant, $storage_item['amount']);
            }
        }

        return $this->purchases;
    }
}
