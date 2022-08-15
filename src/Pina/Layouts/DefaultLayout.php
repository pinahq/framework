<?php

namespace Pina\Layouts;

use Pina\App;
use Pina\Controls\Control;
use Pina\Html;
use Pina\ResourceManagerInterface;

class DefaultLayout extends Control
{

    protected function draw()
    {
        //вначале генерируем тег body, чтобы все контролы внутри body смогли зарегистировать css перед генерацией head
        $body = Html::tag('body', $this->makeBody());
        $head = Html::tag('head', $this->makeMeta() . $this->makeCss());
        $html = Html::tag('html', $head . $body . $this->makeJs());
        return '<!DOCTYPE html>' . "\n" . $html;
    }

    protected function makeBody()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
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
