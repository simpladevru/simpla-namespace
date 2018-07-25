<?php

namespace Root\view;

use Root\api\Simpla;

class NotFoundView extends View
{
    function fetch()
    {
        header("http/1.0 404 not found");

        $page = Simpla::$app->pages->get_page('404');

        if( !empty($page) ) {
            $this->design->assign('page', $page);
            $this->design->assign('meta_title', $page->meta_title);
            $this->design->assign('meta_keywords', $page->meta_keywords);
            $this->design->assign('meta_description', $page->meta_description);
        }

        return $this->design->fetch('404.tpl');
    }
}