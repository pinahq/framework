<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Просто кнопка
 * @package Pina\Controls
 */
class Button extends Control
{
    use ContainerTrait;

    protected $title = '';
    protected $style = 'default';

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    protected function draw()
    {
        return Html::button(
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes(['class' => 'btn btn-' . $this->style])
        );
    }

    protected function drawInner()
    {
        return $this->title;
    }

}
