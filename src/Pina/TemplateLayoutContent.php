<?php

namespace Pina;

class TemplateLayoutContent implements ContentInterface
{
    
    protected $content = '';

    public function setErrors($errors)
    {
    }
    
    public function getType()
    {
        return 'text/html; charset=' . App::charset();
    }
    
    public function setContent($content)
    {
        $this->content = $content;
    }

    public function fetch()
    {
        return $this->content;
    }
    
    public function drawLayout($content)
    {
        static $view = null;
        if (empty($view)) {
            $view = new Templater();
        }
        
        $view->assign("content", $content);
        App::assets()->startLayout();
        $this->content = $view->fetch('Layout/' . Request::getLayout() . '.tpl');
    }

}
