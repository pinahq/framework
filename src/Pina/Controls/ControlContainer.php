<?php

namespace Pina\Controls;

use Pina\Html;

class ControlContainer extends Control
{

    /**
     * Элементы, располагающиеся внутри контейнера данного контрола после внутреннего контента
     * @var Control[]
     */
    protected $inner = [];

    /**
     * Добавить элемент внутри контейнера после основного контента
     * @param Control $control
     * @return $this
     */
    public function append(Control $control)
    {
        array_push($this->inner, $control);
        return $this;
    }

    public function cancelAppend()
    {
        array_pop($this->inner);
    }

    /**
     * Добавить элемент внутри контейна до основного контента
     * @param Control $control
     * @return $this
     */
    public function prepend(Control $control)
    {
        array_unshift($this->inner, $control);
        return $this;
    }

    protected function draw(): string
    {
        return Html::tag('div', $this->drawContent(), $this->makeAttributes());
    }

    /**
     * Отрисовать связанные элементы внутри контейнера до основного контента
     * @return string
     */
    protected function drawContent(): string
    {
        $r = '';
        foreach ($this->inner as $c) {
            $r .= $c;
        }
        return $r;
    }

}