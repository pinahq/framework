<?php

namespace Pina\Controls\Components;

use Pina\Controls\ControlContainer;
use Pina\Html;

/**
 * Таблица
 * @package Pina\Controls
 */
class Table extends ControlContainer
{

    protected function draw(): string
    {
        return Html::tag('table', $this->drawContent(), $this->makeAttributes());
    }

}
