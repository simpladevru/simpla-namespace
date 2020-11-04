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
class Users
{
    // осторожно, при изменении соли испортятся текущие пароли пользователей
    private $salt = '8e86a279d6e182b3c811c559e6b15484';

    function get_users($filter = [])
    {
        $limit           = 1000;
        $page            = 1;
        $group_id_filter = '';
        $keyword_filter  = '';

        if (isset($filter['limit'])) {
            $limit = max(1, intval($filter['limit']));
        }

        if (isset($filter['page'])) {
            $page = max(1, intval($filter['page']));
        }

        if (isset($filter['group_id'])) {
            $group_id_filter = db()->placehold('AND u.group_id in(?@)', (array) $filter['group_id']);
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND (u.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR u.email LIKE "%' . db()->escape(trim($keyword)) . '%"  OR u.last_ip LIKE "%' . db()->escape(trim($keyword)) . '%")');
            }
        }

        $order = 'u.name';
        if (!empty($filter['sort']))
            switch ($filter['sort']) {
                case 'date':
                    $order = 'u.created DESC';
                    break;
                case 'name':
                    $order = 'u.name';
                    break;
            }

        $sql_limit = db()->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);
        // Выбираем пользователей
        $query = db()->placehold("SELECT u.id, u.email, u.password, u.name, u.group_id, u.enabled, u.last_ip, u.created, g.discount, g.name as group_name FROM __users u
		                                LEFT JOIN __groups g ON u.group_id=g.id 
										WHERE 1 $group_id_filter $keyword_filter ORDER BY $order $sql_limit");
        db()->query($query);
        return db()->results();
    }

    function count_users($filter = [])
    {
        $group_id_filter = '';
        $keyword_filter  = '';

        if (isset($filter['group_id'])) {
            $group_id_filter = db()->placehold('AND u.group_id in(?@)', (array) $filter['group_id']);
        }

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword) {
                $keyword_filter .= db()->placehold('AND u.name LIKE "%' . db()->escape(trim($keyword)) . '%" OR u.email LIKE "%' . db()->escape(trim($keyword)) . '%"');
            }
        }

        // Выбираем пользователей
        $query = db()->placehold("SELECT count(*) as count FROM __users u
		                                LEFT JOIN __groups g ON u.group_id=g.id 
										WHERE 1 $group_id_filter $keyword_filter");
        db()->query($query);
        return db()->result('count');
    }

    function get_user($id)
    {
        if (gettype($id) == 'string') {
            $where = db()->placehold(' WHERE u.email=? ', $id);
        } else {
            $where = db()->placehold(' WHERE u.id=? ', intval($id));
        }

        // Выбираем пользователя
        $query = db()->placehold("SELECT u.id, u.email, u.password, u.name, u.group_id, u.enabled, u.last_ip, u.created, g.discount, g.name as group_name FROM __users u LEFT JOIN __groups g ON u.group_id=g.id $where LIMIT 1",
            $id);
        db()->query($query);
        $user = db()->result();
        if (empty($user)) {
            return false;
        }
        $user->discount *= 1; // Убираем лишние нули, чтобы было 5 вместо 5.00
        return $user;
    }

    public function add_user($user)
    {
        $user = (array) $user;
        if (isset($user['password'])) {
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        }

        $query = db()->placehold("SELECT count(*) as count FROM __users WHERE email=?", $user['email']);
        db()->query($query);

        if (db()->result('count') > 0) {
            return false;
        }

        $query = db()->placehold("INSERT INTO __users SET ?%", $user);
        db()->query($query);
        return db()->insert_id();
    }

    public function update_user($id, $user)
    {
        $user = (array) $user;
        if (isset($user['password'])) {
            $user['password'] = md5($this->salt . $user['password'] . md5($user['password']));
        }
        $query = db()->placehold("UPDATE __users SET ?% WHERE id=? LIMIT 1", $user, intval($id));
        db()->query($query);
        return $id;
    }

    /*
    *
    * Удалить пользователя
    * @param $post
    *
    */
    public function delete_user($id)
    {
        if (!empty($id)) {
            $query = db()->placehold("UPDATE __orders SET user_id=NULL WHERE id=? LIMIT 1", intval($id));
            db()->query($query);

            $query = db()->placehold("DELETE FROM __users WHERE id=? LIMIT 1", intval($id));
            if (db()->query($query)) {
                return true;
            }
        }
        return false;
    }

    function get_groups()
    {
        // Выбираем группы
        $query = db()->placehold("SELECT g.id, g.name, g.discount FROM __groups AS g ORDER BY g.discount");
        db()->query($query);
        return db()->results();
    }

    function get_group($id)
    {
        // Выбираем группу
        $query = db()->placehold("SELECT * FROM __groups WHERE id=? LIMIT 1", $id);
        db()->query($query);
        $group = db()->result();

        return $group;
    }

    public function add_group($group)
    {
        $query = db()->placehold("INSERT INTO __groups SET ?%", $group);
        db()->query($query);
        return db()->insert_id();
    }

    public function update_group($id, $group)
    {
        $query = db()->placehold("UPDATE __groups SET ?% WHERE id=? LIMIT 1", $group, intval($id));
        db()->query($query);
        return $id;
    }

    public function delete_group($id)
    {
        if (!empty($id)) {
            $query = db()->placehold("UPDATE __users SET group_id=NULL WHERE group_id=? LIMIT 1", intval($id));
            db()->query($query);

            $query = db()->placehold("DELETE FROM __groups WHERE id=? LIMIT 1", intval($id));
            if (db()->query($query)) {
                return true;
            }
        }
        return false;
    }

    public function check_password($email, $password)
    {
        $encpassword = md5($this->salt . $password . md5($password));
        $query       = db()->placehold("SELECT id FROM __users WHERE email=? AND password=? LIMIT 1", $email,
            $encpassword);
        db()->query($query);
        if ($id = db()->result('id')) {
            return $id;
        }
        return false;
    }

}
