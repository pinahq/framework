<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Строчка таблицы
 * @package Pina\Controls
 */
class TableRow extends Control
{
    use ContainerTrait;

    public function draw()
    {
        return Html::tag(
            'tr',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        return '';
    }

}
