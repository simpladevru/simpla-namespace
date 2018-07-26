<?php

namespace Root\api\components\settings;

/**
 * Class SettingsArraySettings
 * @package Root\api\components\settings
 */
class SettingsArrayStorage implements StorageSettingInterface
{
    private $vars = array();

    function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->vars = [
            'site_name' => 'Великолепный интернет-магазин',
            'company_name' => 'ООО "Великолепный интернет-магазин"',
            'theme' => 'default',
            'products_num' => '24',
            'products_num_admin' => '20',
            'units' => 'шт',
            'date_format' => 'd.m.Y',
            'order_email' => 'me@example.com',
            'comment_email' => 'me@example.com',
            'notify_from_email' => 'me@example.com',
            'decimals_point' => ',',
            'thousands_separator' => '',
            'last_1c_orders_export_date' => '2011-07-30 21:31:56',
            'license' => 'bhbcfgkhfe iomjlglmpl rqwqxrtpz6 898495c7 cfee',
            'max_order_amount' => 50,
            'watermark_offset_x' => 50,
            'watermark_offset_y' => 50,
            'watermark_transparency' => 50,
            'images_sharpen' => 15,
            'admin_email' => 'me@example.com',
        ];
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        if(isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return !empty($this->vars[$name]);
    }
}