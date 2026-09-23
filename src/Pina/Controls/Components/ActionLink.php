<?php

namespace Pina\Controls\Components;

use Pina\Html;

class ActionLink extends ActionButton
{
    protected function draw(): string
    {
        return Html::a(
            $this->drawContent(),
            $this->link,
            $this->makeAttributes()
        );
    }
}