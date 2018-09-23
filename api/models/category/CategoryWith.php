<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 17:28
 */

namespace Root\api\models\category;

use Root\api\Simpla;

class CategoryWith
{
    private $category = null;

    public function __construct($category)
    {
        if( $category instanceof Category ) {
            $this->category = clone $category;
        } elseif ( is_string($category) || is_integer($category) ) {
            $this->category = Simpla::$container->categories->get_category($category);
        }
    }

    public function brands($filter = ['visible' => 1])
    {
        if( empty($this->category->children) ) {
            return $this;
        }

        $this->category->brands = Simpla::$container->brands->get_brands(array_filter(array_merge([
            'category_id' => $this->category->children,
        ], $filter)));

        return $this;
    }

    /**
     * @return Category
     */
    public function get()
    {
        return $this->category;
    }
}