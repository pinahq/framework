<?php

namespace Pina\Controls\Components;

use Pina\Html;

class BodyLessCard extends Card
{
    protected function drawHeader()
    {
        return $this->title ? Html::tag('h3', $this->title) : '';
    }

    protected function draw(): string
    {
        return $this->drawContent();
    }
}