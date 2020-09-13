<?php

namespace Pina\Controls;

use Pina\Html;

class TableCell extends Control
{

    protected $text = '';

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function draw()
    {
        return Html::tag('td', $this->text . $this->compile());
    }

}
