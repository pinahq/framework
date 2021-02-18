<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Таблица
 * @package Pina\Controls
 */
class Table extends Control
{

    public function draw()
    {
        return Html::tag('table', $this->compile(), $this->makeAttributes());
    }

}
