<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Ячейка заголовка таблицы
 * @package Pina\Controls
 */
class TableHeaderCell extends TableCell
{

    public function draw()
    {
        return Html::tag(
            'th',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        return $this->text;
    }

}
