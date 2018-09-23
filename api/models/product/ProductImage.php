<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 18:57
 */

namespace Root\api\models\product;

use Root\api\Simpla;

class ProductImage
{
    public $id;
    public $product_id;
    public $name;
    public $filename;
    public $position;

    public function resize($width=0, $height=0, $set_watermark=false)
    {
        $resized_filename = Simpla::$container->image->add_resize_params($this->filename, $width, $height, $set_watermark);
        $resized_filename_encoded = $resized_filename;

        if(substr($resized_filename_encoded, 0, 7) == 'http://') {
            $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        }

        $resized_filename_encoded = rawurlencode($resized_filename_encoded);

        return Simpla::$container->config->root_url .'/'.
            Simpla::$container->config->resized_images_dir .
            $resized_filename_encoded .'?'.
            Simpla::$container->config->token($resized_filename);
    }

}