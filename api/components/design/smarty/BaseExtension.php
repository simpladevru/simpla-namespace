<?php
/**
 * Created by PhpStorm.
 * User: davinci
 * Date: 23.09.2018
 * Time: 20:07
 */

namespace Root\api\components\design\smarty;

class BaseExtension
{
    protected $smarty;

    public function __construct($smarty)
    {
        $this->smarty = $smarty;
    }
}