<?php

namespace Pina\Controls\Components;

use Pina\Controls\ControlContainer;
use Pina\Html;

/**
 * Обычный список без нумерации
 * @package Pina\Controls
 */
class UnorderedList extends ControlContainer
{

    protected function draw(): string
    {
        $inner = $this->drawContent();
        return $inner ? Html::tag('ul', $inner, $this->makeAttributes()) : '';
    }

}
