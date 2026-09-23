<?php

namespace Pina\Layouts;

use Pina\Controls\ControlContainer;

class EmptyLayout extends ControlContainer
{

    protected function draw(): string
    {
        return $this->drawContent();
    }

}