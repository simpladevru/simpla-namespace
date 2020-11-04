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
class Comments
{
    // Возвращает комментарий по id
    public function get_comment($id)
    {
        $query = db()->placehold("SELECT c.id, c.object_id, c.name, c.ip, c.type, c.text, c.date, c.approved FROM __comments c WHERE id=? LIMIT 1",
            intval($id));

        if (db()->query($query)) {
            return db()->result();
        } else {
            return false;
        }
    }

    // Возвращает комментарии, удовлетворяющие фильтру
    public function get_comments($filter = [])
    {
        // По умолчанию
        $limit            = 0;
        $page             = 1;
        $object_id_filter = '';
        $type_filter      = '';
        $keyword_filter   = '';
        $approved_filter  = '';

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        if (isset($filter['ip'])) {
            $ip = db()->placehold("OR c.ip=?", $filter['ip']);
        }
        if (isset($filter['approved'])) {
            $approved_filter = db()->placehold("AND (c.approved=? $ip)", intval($filter['approved']));
        }

        if ($limit) {
            $sql_limit = db()->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        } else {
            $sql_limit = '';
        }

        if (!empty($filter['object_id'])) {
            $object_id_filter = db()->placehold('AND c.object_id in(?@)', (array) $filter['object_id']);
        }

        if (!empty($filter['type'])) {
            $type_filter = db()->placehold('AND c.type=?', $filter['type']);
        }

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND c.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR c.text LIKE "%' . db()->escape(trim($keyword)) . '%" ');
            }
        }

        $sort = 'DESC';

        $query = db()->placehold("SELECT c.id, c.object_id, c.ip, c.name, c.text, c.type, c.date, c.approved
										FROM __comments c WHERE 1 $object_id_filter $type_filter $keyword_filter $approved_filter ORDER BY id $sort $sql_limit");

        db()->query($query);
        return db()->results();
    }

    // Количество комментариев, удовлетворяющих фильтру
    public function count_comments($filter = [])
    {
        $object_id_filter = '';
        $type_filter      = '';
        $approved_filter  = '';
        $keyword_filter   = '';

        if (!empty($filter['object_id'])) {
            $object_id_filter = db()->placehold('AND c.object_id in(?@)', (array) $filter['object_id']);
        }

        if (!empty($filter['type'])) {
            $type_filter = db()->placehold('AND c.type=?', $filter['type']);
        }

        if (isset($filter['approved'])) {
            $approved_filter = db()->placehold('AND c.approved=?', intval($filter['approved']));
        }

        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND c.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR c.text LIKE "%' . db()->escape(trim($keyword)) . '%" ');
            }
        }

        $query = db()->placehold("SELECT count(distinct c.id) as count
										FROM __comments c WHERE 1 $object_id_filter $type_filter $keyword_filter $approved_filter",
            $this->settings->date_format);

        db()->query($query);
        return db()->result('count');
    }

    // Добавление комментария
    public function add_comment($comment)
    {
        $query = db()->placehold('INSERT INTO __comments
		SET ?%,
		date = NOW()',
            $comment);

        if (!db()->query($query)) {
            return false;
        }

        $id = db()->insert_id();
        return $id;
    }

    // Изменение комментария
    public function update_comment($id, $comment)
    {
        $date_query = '';
        if (isset($comment->date)) {
            $date = $comment->date;
            unset($comment->date);
            $date_query = db()->placehold(', date=STR_TO_DATE(?, ?)', $date, $this->settings->date_format);
        }
        $query = db()->placehold("UPDATE __comments SET ?% $date_query WHERE id in(?@) LIMIT 1", $comment, (array) $id);
        db()->query($query);
        return $id;
    }

    // Удаление комментария
    public function delete_comment($id)
    {
        if (!empty($id)) {
            $query = db()->placehold("DELETE FROM __comments WHERE id=? LIMIT 1", intval($id));
            db()->query($query);
        }
    }
}
