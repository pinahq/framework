<?php

namespace Pina\Controls;

use Pina\Html;

class Form extends Control
{

    protected $action = '';
    protected $method = '';

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function draw()
    {
        return Html::tag('form', $this->compile(), ['action' => $this->action, 'method' => $this->method, 'class' => $this->makeClass('form pina-form')]);
    }

}
