<?php

namespace Pina\Layouts;

use Pina\Controls\Control;
use Pina\Html;
use Pina\StaticResource\Script;
use Pina\StaticResource\Style;
use Pina\Components\Data;

class DefaultLayout extends Control
{

    protected function draw()
    {
        $head = Html::tag('head', $this->makeMeta() . $this->makeCss());
        $html = Html::tag('html', $head . Html::tag('body', $this->makeBody() . $this->makeJs()));
        return '<!DOCTYPE html>' . "\n" . $html;
    }
    
    protected function makeBody()
    {
        return  $this->compile();
    }
    
    protected function place($name, $default = '')
    {
        return isset($this->controls[0]) && $this->controls[0] instanceof Data ? $this->controls[0]->getMeta($name) 
            : (isset($this->after[0]) && $this->after[0] instanceof Data ? $this->after[0]->getMeta($name) : '');
    }

    protected function makeCss()
    {
        return $this->resources()->fetch('css');
    }
    
    protected function makeJs()
    {
        return $this->resources()->fetch('js');
    }
    
    /**
     * 
     * @return \Pina\ResourceManagerInterface
     */
    protected function resources()
    {
        return \Pina\App::container()->get(\Pina\ResourceManagerInterface::class);
    }

    protected function makeMeta()
    {
        return Html::tag('meta', '', ['charset' => 'utf8'])
            . Html::tag('meta', '', ['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'])
            . Html::tag('meta', '', ['name' => "viewport", 'content' => "width=device-width, initial-scale=1"]);
    }

}
