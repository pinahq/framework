<?php

namespace Pina\Response;

use Pina\App;
use Pina\Templater;
use Pina\ResourceManager;

class HtmlResponse extends Response
{

    public $view;

    public function __construct($view = null)
    {
        if (empty($view)) {
            $view = new Templater();
        }

        $this->view = $view;
    }
    
    public function setLayout($layout)
    {
        if (empty($this->view)) {
            return;
        }
        $this->view->setLayout($layout);
    }

    public function fail()
    {
        $message = '';
        foreach ($this->messages as $k => $v) {
            $message .= $v[1]."\r\n";
        }
        
        echo '<html><head><meta charset="'.App::charset().'" /></head><body>'.$message.'</body></html>';
        $this->badRequest();
        exit;
    }

    public function result($name, $value)
    {
        // привязываем к view соответствующие переменные
        $this->view->assign($name, $value);
    }

    public function fetch($handler = '', $first = true)
    {
        $this->header('Pina-Response: html');
        $this->contentType('text/html');
        
        if (empty($handler)) {
            return '';
        }
        
        $this->view->assign('params', \Pina\Request::params());
        $t = $this->view->fetch('pina:' . $handler);
        if ($first) {
            $this->view->assign("content", $t);
            ResourceManager::mode('layout');
            $t = $this->view->fetch('Layout/' . App::get() . '/' . $this->view->getLayout() . '.tpl');
        }

        return $t;
    }
    
    public function fetchTemplate($handler, $first = true)
    {
        $this->header('Pina-Response: html');
        $this->contentType('text/html');
        
        if (empty($handler)) {
            return '';
        }
        
        $this->view->assign('params', \Pina\Request::params());
        $t = $this->view->fetch($handler.'.tpl');
        
        if ($first) {
            $this->view->assign("content", $t);
            ResourceManager::mode('layout');
            $t = $this->view->fetch('Layout/' . App::get() . '/' . $this->view->getLayout() . '.tpl');
        }

        return $t;
    }

}
