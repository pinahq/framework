<?php

namespace Pina\Controls;

use Pina\Html;

class BodyLessCard extends Card
{
    protected function draw()
    {
        $header = '';
        if ($this->title) {
            $header = Html::tag('h3', $this->title);
        }
        return $header . Html::nest('div', $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter());
    }
}