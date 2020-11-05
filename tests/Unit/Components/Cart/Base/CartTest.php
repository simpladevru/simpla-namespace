<?php

namespace Tests\Unit\Components\Cart\Base;

use Api\components\cart\base\Purchase;
use Api\entities\shop\catalog\Variant;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Root\api\components\cart\base\Cart;

class CartTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function test()
    {
        /** @var Cart $cart */
        $cart = simpla('cart');
        $cart->empty_cart();

        $total_price    = 0;
        $total_products = 0;

        $total_purchases = 1000;

        for ($i = 1; $i <= $total_purchases; $i++) {
            $variant        = new Variant();
            $variant->id    = $this->faker->unique()->numberBetween();
            $variant->price = $price = rand(1, 1000);
            $variant->stock = $stock = rand(1, 1000);

            $cart->add_purchase(new Purchase($variant, $amount = $variant->getAvailableStockByAmount(rand(1, 1000))));

            $total_price    += $amount * $price;
            $total_products += $amount;
        }

        $this->assertEquals($cart->get_total_price(), $total_price);
        $this->assertEquals($cart->get_total_products(), $total_products);
        $this->assertEquals($cart->get_purchases()->count(), $total_purchases);
    }

    public function testEmptyCart()
    {
        /** @var Cart $cart */
        $cart = simpla('cart');
        $cart->empty_cart();

        $variant     = new Variant();
        $variant->id = $this->faker->unique()->numberBetween();
        $cart->add_purchase(new Purchase($variant, $amount1 = rand(1, 2)));
        $cart->empty_cart();

        $this->assertEquals($cart->get_total_products(), 0);
    }

    public function testAdd()
    {
        /** @var Cart $cart */
        $cart = simpla('cart');
        $cart->empty_cart();

        $variant        = new Variant();
        $variant->id    = $this->faker->unique()->numberBetween();
        $variant->stock = 10;

        $cart->add_purchase(new Purchase($variant, $amount1 = rand(1, 2)));
        $cart->add_purchase(new Purchase($variant, $amount2 = rand(1, 2)));

        $this->assertEquals($cart->get_total_products(), $amount1 + $amount2);
    }

    public function testUpdate()
    {
        /** @var Cart $cart */
        $cart = simpla('cart');
        $cart->empty_cart();

        $variant        = new Variant();
        $variant->id    = $this->faker->unique()->numberBetween();
        $variant->stock = 10;

        $cart->add_purchase(new Purchase($variant, $amount1 = rand(1, 10)));
        $cart->update_purchase($variant->id, $amount2 = rand(1, 10));

        $this->assertEquals($cart->get_total_products(), $amount2);
    }

    public function testDelete()
    {
        /** @var Cart $cart */
        $cart = simpla('cart');
        $cart->empty_cart();

        $variant        = new Variant();
        $variant->id    = $this->faker->unique()->numberBetween();
        $variant->stock = 10;

        $cart->add_purchase(new Purchase($variant, $amount1 = rand(1, 10)));

        $cart->delete_purchase($variant->id);

        $this->assertEquals($cart->get_total_products(), 0);
    }
}
