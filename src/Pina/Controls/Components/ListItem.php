<?php

namespace Pina\Controls\Components;

use Pina\Controls\Control;
use Pina\Html;

/**
 * Элемент списка
 * @package Pina\Controls
 */
class ListItem extends Control
{

    protected $text = '';

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    protected function draw(): string
    {
        return Html::tag('li', $this->text, $this->makeAttributes());
    }

}
