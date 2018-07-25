<?php

namespace Root\api;

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */

class Pages
{
    public function get_page($id)
	{
		if(gettype($id) == 'string') {
			$where = db()->placehold(' WHERE url=? ', $id);
        }
		else {
			$where = db()->placehold(' WHERE id=? ', intval($id));
        }
		
		$query = "SELECT id, url, header, name, meta_title, meta_description, meta_keywords, body, menu_id, position, visible
		          FROM __pages $where LIMIT 1";

		db()->query($query);
		return db()->result();
	}
	
	/*
	*
	* Функция возвращает массив страниц, удовлетворяющих фильтру
	* @param $filter
	*
	*/
	public function get_pages($filter = array())
	{	
		$menu_filter = '';
		$visible_filter = '';
		$pages = array();

		if(isset($filter['menu_id'])) {
			$menu_filter = db()->placehold('AND menu_id in (?@)', (array)$filter['menu_id']);
        }

		if(isset($filter['visible'])) {
			$visible_filter = db()->placehold('AND visible = ?', intval($filter['visible']));
        }
		
		$query = "SELECT id, url, header, name, meta_title, meta_description, meta_keywords, body, menu_id, position, visible
		          FROM __pages WHERE 1 $menu_filter $visible_filter ORDER BY position";
	
		db()->query($query);
		
		foreach(db()->results() as $page) {
			$pages[$page->id] = $page;
        }
			
		return $pages;
	}

	/*
	*
	* Создание страницы
	*
	*/	
	public function add_page($page)
	{	
		$query = db()->placehold('INSERT INTO __pages SET ?%', $page);
		if(!db()->query($query)) {
			return false;
        }

		$id = db()->insert_id();
		db()->query("UPDATE __pages SET position=id WHERE id=?", $id);	
		return $id;
	}
	
	/*
	*
	* Обновить страницу
	*
	*/
	public function update_page($id, $page)
	{	
		$query = db()->placehold('UPDATE __pages SET ?% WHERE id in (?@)', $page, (array)$id);
		if(!db()->query($query)) {
			return false;
        }
		return $id;
	}
	
	/*
	*
	* Удалить страницу
	*
	*/	
	public function delete_page($id)
	{
		if(!empty($id)) {
			$query = db()->placehold("DELETE FROM __pages WHERE id=? LIMIT 1", intval($id));
			if(db()->query($query)) {
				return true;
            }
		}
		return false;
	}	
	
	/*
	*
	* Функция возвращает массив меню
	*
	*/
	public function get_menus()
	{
		$menus = array();
		$query = "SELECT * FROM __menu ORDER BY position";
		db()->query($query);
		foreach(db()->results() as $menu) {
			$menus[$menu->id] = $menu;
        }
		return $menus;
	}
	
	/*
	*
	* Функция возвращает меню по id
	* @param $id
	*
	*/
	public function get_menu($menu_id)
	{	
		$query = db()->placehold("SELECT * FROM __menu WHERE id=? LIMIT 1", intval($menu_id));
		db()->query($query);
		return db()->result();
	}
	
}
