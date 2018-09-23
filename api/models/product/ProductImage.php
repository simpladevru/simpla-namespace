<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 18:57
 */

namespace Root\api\models\product;

class ProductImage
{
    public $id;
    public $product_id;
    public $name;
    public $filename;
    public $position;

    public function resize($width=0, $height=0, $set_watermark=false)
    {
        return ImageHelper::resize($this->filename, $width, $height, $set_watermark);
    }

}