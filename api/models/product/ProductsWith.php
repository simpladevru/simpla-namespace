<?php

namespace Root\api\models\product;

use Root\api\Simpla;

class ProductsWith
{
    private $products = [];
    private $products_keys = [];

    public function __construct($filter)
    {
        $this->get_products($filter);
    }

    public function get_products($filter)
    {
        $products = Simpla::$container->products->get_products($filter);

        foreach($products as $p) {
            $this->products[$p->id] = $p;
            $this->products_keys[]  = $p->id;
        }
    }

    public function variants()
    {
        foreach(Simpla::$container->variants->get_variants(['product_id' => $this->products_keys]) as $v) {
            if ( isset($this->products[$v->product_id]) ) {
                $this->products[$v->product_id]->variants[$v->id] = $v;
            }
        }
        return $this;
    }

    public function images()
    {
        foreach(Simpla::$container->products->get_images(['product_id'=> $this->products_keys]) as $i) {
            if( isset($this->products[$i->product_id]) ) {
                $this->products[$i->product_id]->images[$i->id] = $i;
            }
        }
        return $this;
    }

    public function comments()
    {
        // ...
        return $this;
    }

    public function features()
    {
        // ..
        return $this;
    }

    public function get()
    {
        foreach($this->products as $id => $product)
        {
            if( !empty($product->variants) ) {
                $product->variant = reset($product->variants);
            }

            if( !empty($product->images) ) {
                $product->image = reset($product->images);
            }
        }

        return $this->products;
    }
}