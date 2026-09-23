<?php

namespace Pina\Controls\Components;

use Pina\Controls\ControlContainer;
use Pina\Html;

/**
 * Строчка таблицы
 * @package Pina\Controls
 */
class TableRow extends ControlContainer
{

    protected function draw(): string
    {
        return Html::tag('tr', $this->drawContent(), $this->makeAttributes());
    }

}
