<?php

namespace Pina\Controls;

use Pina\Html;

class Badge extends Control
{

    protected $text = '';

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    protected function draw()
    {
        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        if (empty($inner)) {
            return '';
        }

        return Html::nest('span.badge', $inner, $this->makeAttributes());
    }

    protected function drawInner()
    {
        return $this->text;
    }

}