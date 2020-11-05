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

        $this->assertEquals($total_price, $cart->get_total_price());
        $this->assertEquals($total_products, $cart->get_total_products());
        $this->assertEquals($total_purchases, $cart->get_purchases()->count());
    }
}
