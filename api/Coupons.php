<?php

namespace Root\api;

/**
 * Simpla CMS
 *
 * @copyright     2012 Denis Pikusov
 * @link          http://simplacms.ru
 * @author        Denis Pikusov
 *
 */
class Coupons
{
    /*
    *
    * Функция возвращает купон по его id или url
    * (в зависимости от типа аргумента, int - id, string - code)
    * @param $id id или code купона
    *
    */
    public function get_coupon($id)
    {
        if (gettype($id) == 'string') {
            $where = db()->placehold('WHERE c.code=? ', $id);
        } else {
            $where = db()->placehold('WHERE c.id=? ', $id);
        }

        $query = db()->placehold("SELECT c.id, c.code, c.value, c.type, c.expire, min_order_price, c.single, c.usages,
										((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single)) AS valid
		                               FROM __coupons c $where LIMIT 1");
        if (db()->query($query)) {
            return db()->result();
        } else {
            return false;
        }
    }

    /*
    *
    * Функция возвращает массив купонов, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function get_coupons($filter = [])
    {
        // По умолчанию
        $limit            = 1000;
        $page             = 1;
        $coupon_id_filter = '';
        $valid_filter     = '';
        $keyword_filter   = '';

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        if (!empty($filter['id'])) {
            $coupon_id_filter = db()->placehold('AND c.id in(?@)', (array) $filter['id']);
        }

        if (isset($filter['valid'])) {
            if ($filter['valid']) {
                $valid_filter = db()->placehold('AND ((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single))');
            } else {
                $valid_filter = db()->placehold('AND NOT ((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single))');
            }
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND (b.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR b.meta_keywords LIKE "%' . db()->escape(trim($keyword)) . '%") ');
            }
        }

        $sql_limit = db()->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = db()->placehold("SELECT c.id, c.code, c.value, c.type, c.expire, min_order_price, c.single, c.usages,
										((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single)) AS valid
		                                      FROM __coupons c WHERE 1 $coupon_id_filter $valid_filter $keyword_filter
		                                      ORDER BY valid DESC, id DESC $sql_limit",
            $this->settings->date_format);

        db()->query($query);
        return db()->results();
    }

    /*
    *
    * Функция вычисляет количество постов, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function count_coupons($filter = [])
    {
        $coupon_id_filter = '';
        $valid_filter     = '';

        if (!empty($filter['id'])) {
            $coupon_id_filter = db()->placehold('AND c.id in(?@)', (array) $filter['id']);
        }

        if (isset($filter['valid'])) {
            $valid_filter = db()->placehold('AND ((DATE(NOW()) <= DATE(c.expire) OR c.expire IS NULL) AND (c.usages=0 OR NOT c.single))');
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND (b.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR b.meta_keywords LIKE "%' . db()->escape(trim($keyword)) . '%") ');
            }
        }

        $query = "SELECT COUNT(distinct c.id) as count
		          FROM __coupons c WHERE 1 $coupon_id_filter $valid_filter";

        if (db()->query($query)) {
            return db()->result('count');
        } else {
            return false;
        }
    }

    /*
    *
    * Создание купона
    * @param $coupon
    *
    */
    public function add_coupon($coupon)
    {
        if (empty($coupon->single)) {
            $coupon->single = 0;
        }
        $query = db()->placehold("INSERT INTO __coupons SET ?%", $coupon);

        if (!db()->query($query)) {
            return false;
        } else {
            return db()->insert_id();
        }
    }

    /*
    *
    * Обновить купон(ы)
    * @param $id, $coupon
    *
    */
    public function update_coupon($id, $coupon)
    {
        $query = db()->placehold("UPDATE __coupons SET ?% WHERE id in(?@) LIMIT ?", $coupon, (array) $id,
            count((array) $id));
        db()->query($query);
        return $id;
    }

    /*
    *
    * Удалить купон
    * @param $id
    *
    */
    public function delete_coupon($id)
    {
        if (!empty($id)) {
            $query = db()->placehold("DELETE FROM __coupons WHERE id=? LIMIT 1", intval($id));
            return db()->query($query);
        }
    }

}
