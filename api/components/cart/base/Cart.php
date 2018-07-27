<?php

namespace Root\api\components\cart\base;

use Root\api\Coupons;
use Root\api\Products;
use Root\api\Users;
use Root\api\Variants;

class Cart
{
    private $storage;
    private $products;
    private $variants;
    private $users;
    private $coupons;

    public function __construct(
        CartStorageInterface $storage,
        Products $products,
        Variants $variants,
        Users $users,
        Coupons $coupons
    )
    {
        $this->storage = $storage;

        $this->products = $products;
        $this->variants = $variants;
        $this->coupons = $coupons;
    }

    /**
     * @return \stdClass
     */
    public function get_cart()
    {
        $cart = new \stdClass();
        $cart->purchases = array();
        $cart->total_price = 0;
        $cart->total_products = 0;
        $cart->coupon = null;
        $cart->discount = 0;
        $cart->coupon_discount = 0;

        // Берем из сессии список variant_id=>amount
        if( $this->storage->has_items() )
        {
            $storage_items = $this->storage->get_items();

            $variants = $this->variants->get_variants(array('id'=>array_keys($storage_items)));
            if(!empty($variants))
            {
                foreach($variants as $variant)
                {
                    $items[$variant->id] = new \stdClass();
                    $items[$variant->id]->variant = $variant;
                    $items[$variant->id]->amount = $storage_items[$variant->id]['amount'];
                    $products_ids[] = $variant->product_id;
                }

                $products = array();
                foreach($this->products->get_products(array('id'=>$products_ids, 'limit' => count($products_ids))) as $p)
                    $products[$p->id]=$p;

                $images = $this->products->get_images(array('product_id'=>$products_ids));
                foreach($images as $image)
                    $products[$image->product_id]->images[$image->id] = $image;

                foreach($items as $variant_id=>$item)
                {
                    $purchase = null;
                    if(!empty($products[$item->variant->product_id]))
                    {
                        $purchase = new \stdClass();
                        $purchase->product = $products[$item->variant->product_id];
                        $purchase->variant = $item->variant;
                        $purchase->amount = $item->amount;

                        $cart->purchases[] = $purchase;
                        $cart->total_price += $item->variant->price*$item->amount;
                        $cart->total_products += $item->amount;
                    }
                }

                // Пользовательская скидка
                $cart->discount = 0;
                if(isset($_SESSION['user_id']) && $user = $this->users->get_user(intval($_SESSION['user_id'])))
                    $cart->discount = $user->discount;

                $cart->total_price *= (100-$cart->discount)/100;

                // Скидка по купону
                if( $this->storage->has_coupon_code() )
                {
                    $cart->coupon = $this->coupons->get_coupon($this->storage->get_coupon_code());
                    if($cart->coupon && $cart->coupon->valid && $cart->total_price>=$cart->coupon->min_order_price)
                    {
                        if($cart->coupon->type=='absolute')
                        {
                            // Абсолютная скидка не более суммы заказа
                            $cart->coupon_discount = $cart->total_price>$cart->coupon->value?$cart->coupon->value:$cart->total_price;
                            $cart->total_price = max(0, $cart->total_price-$cart->coupon->value);
                        }
                        else
                        {
                            $cart->coupon_discount = $cart->total_price * ($cart->coupon->value)/100;
                            $cart->total_price = $cart->total_price-$cart->coupon_discount;
                        }
                    }
                    else
                    {
                        $this->storage->delete_coupon_code();
                    }
                }

            }
        }

        return $cart;
    }

    /**
     * @param $variant_id
     * @param int $amount
     */
    public function add_item($variant_id, $amount = 1)
    {
        $amount = max(1, $amount);

        if( $this->storage->has_item($variant_id) ) {
            $item = $this->storage->get($variant_id);
            $amount = max(1, $amount + $item['amount']);
        }

        $variant = $this->variants->get_variant($variant_id);

        if(!empty($variant) && ($variant->stock>0) ) {
            $amount = min($amount, $variant->stock);
            $this->storage->update_item($variant_id, $amount);
        }
    }

    /**
     * @param $variant_id
     * @param int $amount
     */
    public function update_item($variant_id, $amount = 1)
    {
        $amount = max(1, $amount);
        $variant = $this->variants->get_variant($variant_id);
        if(!empty($variant) && $variant->stock>0) {
            $amount = min($amount, $variant->stock);
            $this->storage->update_item($variant_id, $amount);
        }
    }


    /**
     * @param $variant_id
     */
    public function delete_item($variant_id)
    {
        $this->storage->delete_item($variant_id);
    }

    /**
     * @return mixed
     */
    public function empty_cart()
    {
        return $this->storage->clear();
    }

    /**
     * @param $coupon_code
     */
    public function apply_coupon($coupon_code)
    {
        $coupon = $this->coupons->get_coupon((string)$coupon_code);
        if($coupon && $coupon->valid) {
            $this->storage->set_coupon_code($coupon->code);
        }
        else {
            $this->storage->delete_coupon_code();
        }
    }
}