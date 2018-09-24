<?PHP

namespace Root\view;
use Root\api\facades\Pages;
use Root\api\Simpla;
use Root\helpers\Debug;

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 * Этот класс использует шаблон page.tpl
 *
 */

class PageView extends View
{
	function fetch()
	{
		$url = $this->request->get('page_url', 'string');

		//$page = $this->pages->get_page($url);
        //$page = Pages::get_page($url);

        $page = Simpla::getInstance()->pages->get_page($url);

		// Отображать скрытые страницы только админу
		if(empty($page) || (!$page->visible && empty($_SESSION['admin']))) {
			return false;
        }
		
		$this->design->assign('page', $page);
		$this->design->assign('meta_title', $page->meta_title);
		$this->design->assign('meta_keywords', $page->meta_keywords);
		$this->design->assign('meta_description', $page->meta_description);
		
		return $this->design->fetch('page.tpl');
	}
}