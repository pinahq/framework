<?php

namespace Pina\Controls;

use Pina\Html;

class TableHeaderCell extends Control
{

    protected $text = '';

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function draw()
    {
        return Html::tag('th', $this->text . $this->compile());
    }

}
