<?php

namespace Pina\Controls;

use Pina\Html;

class Button extends Control
{

    protected $title = '';

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function draw()
    {
        return Html::button($this->title . $this->compile(), ['class' => 'btn btn-primary']);
    }

}
