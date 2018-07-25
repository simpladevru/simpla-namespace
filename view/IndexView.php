<?PHP

namespace Root\view;
use Root\api\Simpla;

/**
 * Simpla CMS
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simp.la
 * @author 		Denis Pikusov
 *
 * Этот класс использует шаблон index.tpl,
 * который содержит всю страницу кроме центрального блока
 * По get-параметру module мы определяем что сожержится в центральном блоке
 *
 */

class IndexView
{	
	public $modules_dir = 'view/';

	private $module;
	private $body;

	function fetch()
	{
	    $module = $this->get_module();

        if ( !($content = $module->fetch()) ) {
            $content = $this->not_found()->fetch();
		}

        Simpla::$app->design->assign('content', $content);
        Simpla::$app->design->assign('module', $this->module);

		if(is_null( $wrapper = Simpla::$app->design->get_var('wrapper') )) {
			$wrapper = 'index.tpl';
        }

        $this->body = !empty($wrapper)
            ? Simpla::$app->design->fetch($wrapper)
            : $content;

        $this->print_result($this->body);
	}

	public function get_module()
    {
        $this->module = Simpla::$app->request->get('module', 'string');
        $this->module = preg_replace("/[^A-Za-z0-9]+/", "", $this->module);

        $module = "Root\\view\\" . $this->module;

        if(!class_exists($module)) {
            return false;
        }
        return new $module;
    }

    /**
     * @return NotFoundView
     */
	public function not_found()
    {
        return new NotFoundView();
    }

    /**
     * @param $res
     */
	public function print_result($res)
    {
        // Выводим результат
        header("Content-type: text/html; charset=UTF-8");

        print $res;

        // Сохраняем последнюю просмотренную страницу в переменной $_SESSION['last_visited_page']
        if(empty($_SESSION['last_visited_page']) || empty($_SESSION['current_page']) || $_SERVER['REQUEST_URI'] !== $_SESSION['current_page']) {
            if(!empty($_SESSION['current_page']) && !empty($_SESSION['last_visited_page']) && $_SESSION['last_visited_page'] !== $_SESSION['current_page']) {
                $_SESSION['last_visited_page'] = $_SESSION['current_page'];
            }
            $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
        }
    }
}
