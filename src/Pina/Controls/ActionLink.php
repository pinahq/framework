<?php

namespace Pina\Controls;

use Pina\Html;

class ActionLink extends ActionButton
{
    protected function draw()
    {
        return Html::a(
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->link,
            $this->makeAttributes()
        );
    }
}