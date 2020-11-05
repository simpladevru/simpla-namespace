<?php

namespace Tests\Unit\Components\Cart\Base;

use Api\components\cart\base\Purchase;
use Api\entities\shop\catalog\Image;
use Api\entities\shop\catalog\Product;
use Api\entities\shop\catalog\Variant;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class PurchaseTest extends TestCase
{
    public function testStructure()
    {
        $product         = new Product();
        $product->id     = rand(1, 1000);
        $product->images = new Collection([$image = new Image()]);

        $variant          = new Variant();
        $variant->id      = $id = rand(1, 1000);
        $variant->price   = $price = rand(1, 1000);
        $variant->stock   = $stock = rand(1, 1000);
        $variant->product = $product;

        $purchase = new Purchase($variant, $amount = min($stock, rand(1, 1000)));

        $this->assertEquals($id, $purchase->get_id());
        $this->assertEquals($amount, $purchase->get_amount());
        $this->assertEquals($price * $amount, $purchase->get_cost());
        $this->assertEquals($variant, $purchase->get_variant());
        $this->assertEquals($product, $purchase->get_product());
        $this->assertEquals($image, $purchase->get_image());
    }

    public function testAmountGreaterStock()
    {
        $variant        = new Variant();
        $variant->id    = $id = rand(1, 1000);
        $variant->stock = $stock = rand(1, 1000);

        $purchase = new Purchase($variant, $amount = rand($stock, $stock + rand(1, 1000)));

        $this->assertEquals($stock, $purchase->get_amount());
    }

    public function testAddAmount()
    {
        $variant        = new Variant();
        $variant->id    = $id = rand(1, 1000);
        $variant->stock = $stock = rand(1, 1000);

        $purchase = new Purchase($variant, $stock - 1);
        $purchase->add_amount(2);

        $this->assertEquals($stock, $purchase->get_amount());
    }
}
