<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 19:09
 */

namespace Root\api\models\product;

use Root\api\Config;
use Root\api\Simpla;

class ImageHelper
{
    public static function resize($filename, $width=0, $height=0, $set_watermark=false)
    {
        $resized_filename = Simpla::$container->image->add_resize_params($filename, $width, $height, $set_watermark);
        $resized_filename_encoded = $resized_filename;

        if(substr($resized_filename_encoded, 0, 7) == 'http://') {
            $resized_filename_encoded = rawurlencode($resized_filename_encoded);
        }

        $resized_filename_encoded = rawurlencode($resized_filename_encoded);

        /** @var Config $config */
        $config = Simpla::$container->config;

        return $config->root_url .'/'.
            $config->resized_images_dir .
            $resized_filename_encoded .'?'.
            $config->token($resized_filename);
    }
}