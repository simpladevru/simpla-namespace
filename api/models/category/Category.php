<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 17:45
 */

namespace Root\api\models\category;

/**
 * Class Category
 * @package Root\api\models\category
 */
class Category
{
    public $visible;
    public $children = [];

    public function visibleOrAdmin()
    {
        if ( !$this->isVisible() && empty($_SESSION['admin']) ) {
            return false;
        }
        return true;
    }

    public function isVisible()
    {
        return $this->visible ? true : false;
    }
}