<?php

namespace Pina\Controls\Components;

use Pina\Controls\Control;
use Pina\Html;

/**
 * Просто кнопка
 * @package Pina\Controls
 */
class Button extends Control
{
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

    protected function draw(): string
    {
        return Html::button($this->drawContent(), $this->makeAttributes(['class' => 'btn btn-' . $this->style]));
    }

    protected function drawContent(): string
    {
        return $this->title;
    }

}
