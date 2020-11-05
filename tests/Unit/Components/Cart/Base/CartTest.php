<?php

namespace Tests\Unit\Components\Cart\Base;

use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testGetTotalPrice()
    {
        $this->assertEquals(0, simpla('cart')->get_total_price());
    }

    public function testGetTotalProducts()
    {
        $this->assertEquals(0, simpla('cart')->get_total_products());
    }

    public function testHasPurchase()
    {
        $this->assertFalse(simpla('cart')->has_purchases());
    }
}
