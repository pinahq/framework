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
        return Html::nest('span.badge',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        return $this->text;
    }

}