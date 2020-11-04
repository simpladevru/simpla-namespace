<?php

namespace Root\api;

/**
 * Simpla CMS
 *
 * @copyright     2011 Denis Pikusov
 * @link          http://simplacms.ru
 * @author        Denis Pikusov
 *
 */
class Delivery
{
    public function get_delivery($id)
    {
        $query = db()->placehold("SELECT id, name, description, free_from, price, enabled, position, separate_payment FROM __delivery WHERE id=? LIMIT 1",
            intval($id));
        db()->query($query);
        return db()->result();
    }

    public function get_deliveries($filter = [])
    {
        // По умолчанию
        $enabled_filter = '';

        if (!empty($filter['enabled'])) {
            $enabled_filter = db()->placehold('AND enabled=?', intval($filter['enabled']));
        }

        $query = "SELECT id, name, description, free_from, price, enabled, position, separate_payment
					FROM __delivery WHERE 1 $enabled_filter ORDER BY position";

        db()->query($query);

        return db()->results();
    }

    public function update_delivery($id, $delivery)
    {
        $query = db()->placehold("UPDATE __delivery SET ?% WHERE id in(?@)", $delivery, (array) $id);
        db()->query($query);
        return $id;
    }

    public function add_delivery($delivery)
    {
        $query = db()->placehold('INSERT INTO __delivery SET ?%', $delivery);

        if (!db()->query($query)) {
            return false;
        }

        $id = db()->insert_id();
        db()->query("UPDATE __delivery SET position=id WHERE id=?", intval($id));
        return $id;
    }

    public function delete_delivery($id)
    {
        $query = db()->placehold("DELETE FROM __delivery WHERE id=? LIMIT 1", intval($id));
        db()->query($query);
    }

    public function get_delivery_payments($id)
    {
        $query = db()->placehold("SELECT payment_method_id FROM __delivery_payment WHERE delivery_id=?", intval($id));
        db()->query($query);
        return db()->results('payment_method_id');
    }

    public function update_delivery_payments($id, $payment_methods_ids)
    {
        $query = db()->placehold("DELETE FROM __delivery_payment WHERE delivery_id=?", intval($id));
        db()->query($query);
        if (is_array($payment_methods_ids)) {
            foreach ($payment_methods_ids as $p_id) {
                db()->query("INSERT INTO __delivery_payment SET delivery_id=?, payment_method_id=?", $id, $p_id);
            }
        }
    }

}
