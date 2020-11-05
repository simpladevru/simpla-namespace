<?php

namespace Root\tests\Unit\Entities\Shop\Catalog;

use Api\entities\shop\catalog\Variant;
use PHPUnit\Framework\TestCase;

class VariantTest extends TestCase
{
    public function testStockNull()
    {
        $variant        = new Variant();
        $variant->stock = $stock = null;

        $this->assertEquals(settings()->max_order_amount, $variant->getStock());
    }

    public function testStockNotNull()
    {
        $variant        = new Variant();
        $variant->stock = $stock = rand(1, 50);

        $this->assertEquals($stock, $variant->getStock());
    }

    public function testAvailableStockByAmount()
    {
        $variant        = new Variant();
        $variant->stock = $stock = rand(1, 100);

        $this->assertEquals($stock, $variant->getAvailableStockByAmount(101));
    }

    public function testAvailableStockByAmountMaxOrderAmount()
    {
        $variant        = new Variant();
        $variant->stock = null;

        $this->assertEquals(settings()->max_order_amount, $variant->getAvailableStockByAmount(100));
    }
}