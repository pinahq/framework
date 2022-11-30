<?php

namespace Pina\Controls;

use Pina\Html;

class FormRow extends Control
{
    protected function draw()
    {
        $wrapper = new Wrapper('.col');
        return Html::nest(
            '.row',
            $this->drawInnerBefore($wrapper) . $this->drawInner() . $this->drawInnerAfter($wrapper)
        );
    }
}