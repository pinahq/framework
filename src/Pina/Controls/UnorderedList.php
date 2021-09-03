<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Обычный список без нумерации
 * @package Pina\Controls
 */
class UnorderedList extends Control
{
    use ContainerTrait;

    protected function draw()
    {
        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        return $inner ? Html::tag('ul', $inner, $this->makeAttributes()) : '';
    }

    protected function drawInner()
    {
        return '';
    }

}
