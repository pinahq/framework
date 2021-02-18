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
        return Html::tag('th', $this->text . $this->compile(), $this->makeAttributes());
    }

}
