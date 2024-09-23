<?php

namespace Pina\Layouts;

use Pina\Controls\Control;

class EmptyLayout extends Control
{

    protected function draw()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

}