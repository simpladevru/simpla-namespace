<?php

namespace Root\api;

/**
 * Simpla CMS
 *
 * @copyright     2013 Denis Pikusov
 * @link          http://simplacms.ru
 * @author        Denis Pikusov
 *
 */
class Feedbacks
{
    public function get_feedback($id)
    {
        $query = db()->placehold("SELECT f.id, f.name, f.email, f.ip, f.message, f.date FROM __feedbacks f WHERE id=? LIMIT 1",
            intval($id));

        if (db()->query($query)) {
            return db()->result();
        } else {
            return false;
        }
    }

    public function get_feedbacks($filter = [], $new_on_top = false)
    {
        // По умолчанию
        $limit          = 0;
        $page           = 1;
        $keyword_filter = '';

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        $sql_limit = db()->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND f.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR f.message LIKE "%' . db()->escape(trim($keyword)) . '%" OR f.email LIKE "%' . db()->escape(trim($keyword)) . '%" ');
            }
        }

        if ($new_on_top) {
            $sort = 'DESC';
        } else {
            $sort = 'ASC';
        }

        $query = db()->placehold("SELECT f.id, f.name, f.email, f.ip, f.message, f.date
										FROM __feedbacks f WHERE 1 $keyword_filter ORDER BY f.id $sort $sql_limit");

        db()->query($query);
        return db()->results();
    }

    public function count_feedbacks($filter = [])
    {
        $keyword_filter = '';

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND f.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR f.message LIKE "%' . db()->escape(trim($keyword)) . '%" OR f.email LIKE "%' . db()->escape(trim($keyword)) . '%" ');
            }
        }

        $query = db()->placehold("SELECT count(distinct f.id) as count
										FROM __feedbacks f WHERE 1 $keyword_filter");

        db()->query($query);
        return db()->result('count');
    }

    public function add_feedback($feedback)
    {
        $query = db()->placehold('INSERT INTO __feedbacks
		SET ?%,
		date = NOW()',
            $feedback);

        if (!db()->query($query)) {
            return false;
        }

        $id = db()->insert_id();
        return $id;
    }

    public function update_feedback($id, $feedback)
    {
        $date_query = '';
        if (isset($feedback->date)) {
            $date = $feedback->date;
            unset($feedback->date);
            $date_query = db()->placehold(', date=STR_TO_DATE(?, ?)', $date, $this->settings->date_format);
        }
        $query = db()->placehold("UPDATE __feedbacks SET ?% $date_query WHERE id in(?@) LIMIT 1", $feedback,
            (array) $id);
        db()->query($query);
        return $id;
    }

    public function delete_feedback($id)
    {
        if (!empty($id)) {
            $query = db()->placehold("DELETE FROM __feedbacks WHERE id=? LIMIT 1", intval($id));
            db()->query($query);
        }
    }
}
