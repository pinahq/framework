<?php

namespace Pina\Legacy;

use Pina\App;
use Pina\ContentInterface;

class TemplaterContent implements ContentInterface
{

    protected $view;
    protected $content = '';

    public function __construct(&$results, $template, $useLayout)
    {
        static $view = null;
        if (empty($view)) {
            $view = \Pina\App::container()->has(\Smarty::class) ? \Pina\App::container()->get(\Smarty::class) : new Templater;
        }

        $this->view = $view;
        $this->view->assign($results);
        $this->view->assign('params', Request::all());

        $this->content = $this->view->fetch($template);

        if ($useLayout) {
            $this->view->assign("content", $this->content);
            App::assets()->startLayout();
            $this->content = $this->view->fetch('Layout/' . Request::getLayout() . '.tpl');
        }
    }

    public function setErrors($errors)
    {
        $this->view->assign('errors', $errors);
    }

    public function getType()
    {
        return 'text/html; charset=' . App::charset();
    }

    public function fetch()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function drawLayout($content)
    {
        $this->view->assign("content", $content);
        App::assets()->startLayout();
        $this->content = $this->view->fetch('Layout/' . Request::getLayout() . '.tpl');
    }

}