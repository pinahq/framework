<?php

namespace Pina\Controls\Components;

use Pina\Controls\Control;
use Pina\Html;

class Badge extends Control
{

    protected $text = '';

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    protected function draw(): string
    {
        $content = $this->drawContent();
        return $content ? Html::nest('span.badge', $content, $this->makeAttributes()) : '';
    }

    protected function drawContent(): string
    {
        return $this->text;
    }

}