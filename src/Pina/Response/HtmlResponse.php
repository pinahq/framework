<?php

namespace Pina\Response;

use Pina\App;
use Pina\Request;
use Pina\Templater;
use Pina\ResourceManager;

class HtmlResponse extends Response
{

    protected $view;

    public function __construct($view = null)
    {
        if (empty($view)) {
            $view = new Templater();
        }

        $this->view = $view;
    }

    public function fetch(&$results, $controller, $action, $display, $isExternal)
    {
        $this->view->assign($results);
        $this->view->assign('params', \Pina\Request::params());
        $t = $this->view->fetch('pina:' . $controller.'!'.$action.'!'.$display);
        if ($isExternal) {
            $this->view->assign("content", $t);
            ResourceManager::mode('layout');
            $t = $this->view->fetch('Layout/' . App::get() . '/' . Request::getLayout() . '.tpl');
        }

        return $t;
    }
    
    public function fetchTemplate(&$results, $handler)
    {
        $this->view->assign($results);
        $this->view->assign('params', \Pina\Request::params());
        $t = $this->view->fetch($handler.'.tpl');
        return $t;
    }

}
