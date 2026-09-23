<?php

namespace Pina\Controls\Components;

use Pina\Controls\Control;
use Pina\Html;

/**
 * Ячейка таблицы
 * @package Pina\Controls
 */
class TableCell extends Control
{

    protected $text = '';

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text)
    {
        $this->text = $text;
        return $this;
    }

    protected function draw(): string
    {
        return Html::tag('td', $this->drawContent(), $this->makeAttributes());
    }

    protected function drawContent(): string
    {
        return $this->text;
    }

}
