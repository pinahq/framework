<?php

namespace Pina\Controls\Components;

use Pina\Html;

/**
 * Ячейка заголовка таблицы
 * @package Pina\Controls
 */
class TableHeaderCell extends TableCell
{

    protected function draw(): string
    {
        return Html::tag(
            'th',
            $this->drawContent(),
            $this->makeAttributes()
        );
    }

    protected function drawContent(): string
    {
        return $this->text;
    }

}
