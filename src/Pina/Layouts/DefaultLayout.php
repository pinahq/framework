<?php

namespace Pina\Layouts;

use Pina\App;
use Pina\Controls\ContainerTrait;
use Pina\Controls\Control;
use Pina\Html;
use Pina\Components\Data;
use Pina\ResourceManagerInterface;

class DefaultLayout extends Control
{
    use ContainerTrait;

    protected function draw()
    {
        $head = Html::tag('head', $this->makeMeta() . $this->makeCss());
        $html = Html::tag('html', $head . Html::tag('body', $this->makeBody() . $this->makeJs()));
        return '<!DOCTYPE html>' . "\n" . $html;
    }

    protected function makeBody()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

    protected function drawInner()
    {
        return '';
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
     * @return ResourceManagerInterface
     */
    protected function resources()
    {
        return App::container()->get(ResourceManagerInterface::class);
    }

    protected function makeMeta()
    {
        return Html::tag('meta', '', ['charset' => 'utf8'])
            . Html::tag('meta', '', ['http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'])
            . Html::tag('meta', '', ['name' => "viewport", 'content' => "width=device-width, initial-scale=1"]);
    }

}
