<?php

namespace Pina\Controls;

use Pina\Html;

class ListItem extends Control
{

    protected $text = '';

    public function setText(&$text)
    {
        $this->text = $text;
        return $this;
    }

    public function draw()
    {
        return Html::tag('li', $this->text . $this->compile());
    }

}
