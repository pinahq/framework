<?php

namespace Pina\Controls;

use Pina\Html;

class Paragraph extends Control
{

    protected $text = '';

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function draw()
    {
        return Html::tag('p', $this->text . $this->compile());
    }

}