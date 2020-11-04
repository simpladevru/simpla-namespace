<?php

namespace Root\api;

use Root\helpers\Debug;

/**
 * Работа с вариантами товаров
 *
 * @copyright     2011 Denis Pikusov
 * @link          http://simplacms.ru
 * @author        Denis Pikusov
 *
 */
class Variants
{
    /**
     * @param array $filter
     * @return array|bool
     */
    public function get_variants($filter = [])
    {
        $product_id_filter = '';
        $variant_id_filter = '';
        $instock_filter    = '';

        if (!empty($filter['product_id'])) {
            $product_id_filter = db()->placehold('AND v.product_id in(?@)', (array) $filter['product_id']);
        }

        if (!empty($filter['id'])) {
            $variant_id_filter = db()->placehold('AND v.id in(?@)', (array) $filter['id']);
        }

        if (!empty($filter['in_stock']) && $filter['in_stock']) {
            $instock_filter = db()->placehold('AND (v.stock>0 OR v.stock IS NULL)');
        }

        if (!$product_id_filter && !$variant_id_filter) {
            return [];
        }

        $query = db()->placehold("SELECT v.id, v.product_id , v.price, NULLIF(v.compare_price, 0) as compare_price, v.sku, IFNULL(v.stock, ?) as stock, (v.stock IS NULL) as infinity, v.name, v.attachment, v.position
					FROM __variants AS v
					WHERE 
					1
					$product_id_filter          
					$variant_id_filter  
					$instock_filter 
					ORDER BY v.position       
					", Simpla::$container->settings->max_order_amount);

        db()->query($query);
        return db()->results();
    }

    public function get_variant($id)
    {
        if (empty($id)) {
            return false;
        }

        $query = db()->placehold("SELECT v.id, v.product_id , v.price, NULLIF(v.compare_price, 0) as compare_price, v.sku, IFNULL(v.stock, ?) as stock, (v.stock IS NULL) as infinity, v.name, v.attachment
					FROM __variants v WHERE v.id=?
					LIMIT 1", settings()->max_order_amount, $id);

        db()->query($query);
        $variant = db()->result();
        return $variant;
    }

    public function update_variant($id, $variant)
    {
        $query = db()->placehold("UPDATE __variants SET ?% WHERE id=? LIMIT 1", $variant, intval($id));
        db()->query($query);
        return $id;
    }

    public function add_variant($variant)
    {
        $query = db()->placehold("INSERT INTO __variants SET ?%", $variant);
        db()->query($query);
        return db()->insert_id();
    }

    public function delete_variant($id)
    {
        if (!empty($id)) {
            $this->delete_attachment($id);
            $query = db()->placehold("DELETE FROM __variants WHERE id = ? LIMIT 1", intval($id));
            db()->query($query);
        }
    }

    public function delete_attachment($id)
    {
        $query = db()->placehold("SELECT attachment FROM __variants WHERE id=?", $id);
        db()->query($query);
        $filename = db()->result('attachment');
        $query    = db()->placehold("SELECT 1 FROM __variants WHERE attachment=? AND id!=?", $filename, $id);
        db()->query($query);
        $exists = db()->num_rows();
        if (!empty($filename) && $exists == 0) {
            @unlink($this->config->root_dir . '/' . $this->config->downloads_dir . $filename);
        }
        $this->update_variant($id, ['attachment' => null]);
    }

}
