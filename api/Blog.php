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
class Blog
{
    /*
    *
    * Функция возвращает пост по его id или url
    * (в зависимости от типа аргумента, int - id, string - url)
    * @param $id id или url поста
    *
    */
    public function get_post($id)
    {
        if (is_int($id)) {
            $where = db()->placehold(' WHERE b.id=? ', intval($id));
        } else {
            $where = db()->placehold(' WHERE b.url=? ', $id);
        }

        $query = db()->placehold("SELECT b.id, b.url, b.name, b.annotation, b.text, b.meta_title,
		                               b.meta_keywords, b.meta_description, b.visible, b.date
		                               FROM __blog b $where LIMIT 1");
        if (db()->query($query)) {
            return db()->result();
        } else {
            return false;
        }
    }

    /*
    *
    * Функция возвращает массив постов, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function get_posts($filter = [])
    {
        // По умолчанию
        $limit          = 1000;
        $page           = 1;
        $post_id_filter = '';
        $visible_filter = '';
        $keyword_filter = '';
        $posts          = [];

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        if (!empty($filter['id'])) {
            $post_id_filter = db()->placehold('AND b.id in(?@)', (array) $filter['id']);
        }

        if (isset($filter['visible'])) {
            $visible_filter = db()->placehold('AND b.visible = ?', intval($filter['visible']));
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND (b.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR b.meta_keywords LIKE "%' . db()->escape(trim($keyword)) . '%") ');
            }
        }

        $sql_limit = db()->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = db()->placehold("SELECT b.id, b.url, b.name, b.annotation, b.text,
		                                      b.meta_title, b.meta_keywords, b.meta_description, b.visible,
		                                      b.date
		                                      FROM __blog b WHERE 1 $post_id_filter $visible_filter $keyword_filter
		                                      ORDER BY date DESC, id DESC $sql_limit");

        db()->query($query);
        return db()->results();
    }

    /*
    *
    * Функция вычисляет количество постов, удовлетворяющих фильтру
    * @param $filter
    *
    */
    public function count_posts($filter = [])
    {
        $post_id_filter = '';
        $visible_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id'])) {
            $post_id_filter = db()->placehold('AND b.id in(?@)', (array) $filter['id']);
        }

        if (isset($filter['visible'])) {
            $visible_filter = db()->placehold('AND b.visible = ?', intval($filter['visible']));
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND (b.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR b.meta_keywords LIKE "%' . db()->escape(trim($keyword)) . '%") ');
            }
        }

        $query = "SELECT COUNT(distinct b.id) as count
		          FROM __blog b WHERE 1 $post_id_filter $visible_filter $keyword_filter";

        if (db()->query($query)) {
            return db()->result('count');
        } else {
            return false;
        }
    }

    /*
    *
    * Создание поста
    * @param $post
    *
    */
    public function add_post($post)
    {
        if (!isset($post->date)) {
            $date_query = ', date=NOW()';
        } else {
            $date_query = '';
        }
        $query = db()->placehold("INSERT INTO __blog SET ?% $date_query", $post);

        if (!db()->query($query)) {
            return false;
        } else {
            return db()->insert_id();
        }
    }

    /*
    *
    * Обновить пост(ы)
    * @param $post
    *
    */
    public function update_post($id, $post)
    {
        $query = db()->placehold("UPDATE __blog SET ?% WHERE id in(?@) LIMIT ?", $post, (array) $id,
            count((array) $id));
        db()->query($query);
        return $id;
    }

    /*
    *
    * Удалить пост
    * @param $id
    *
    */
    public function delete_post($id)
    {
        if (!empty($id)) {
            $query = db()->placehold("DELETE FROM __blog WHERE id=? LIMIT 1", intval($id));
            if (db()->query($query)) {
                $query = db()->placehold("DELETE FROM __comments WHERE type='blog' AND object_id=?", intval($id));
                if (db()->query($query)) {
                    return true;
                }
            }
        }
        return false;
    }

    /*
    *
    * Следующий пост
    * @param $post
    *
    */
    public function get_next_post($id)
    {
        db()->query("SELECT date FROM __blog WHERE id=? LIMIT 1", $id);
        $date = db()->result('date');

        db()->query("(SELECT id FROM __blog WHERE date=? AND id>? AND visible  ORDER BY id limit 1)
		                   UNION
		                  (SELECT id FROM __blog WHERE date>? AND visible ORDER BY date, id limit 1)",
            $date, $id, $date);
        $next_id = db()->result('id');
        if ($next_id) {
            return $this->get_post(intval($next_id));
        } else {
            return false;
        }
    }

    /*
    *
    * Предыдущий пост
    * @param $post
    *
    */
    public function get_prev_post($id)
    {
        db()->query("SELECT date FROM __blog WHERE id=? LIMIT 1", $id);
        $date = db()->result('date');

        db()->query("(SELECT id FROM __blog WHERE date=? AND id<? AND visible ORDER BY id DESC limit 1)
		                   UNION
		                  (SELECT id FROM __blog WHERE date<? AND visible ORDER BY date DESC, id DESC limit 1)",
            $date, $id, $date);
        $prev_id = db()->result('id');
        if ($prev_id) {
            return $this->get_post(intval($prev_id));
        } else {
            return false;
        }
    }
}
