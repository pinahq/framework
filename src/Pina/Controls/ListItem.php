<?php

namespace Pina\Controls;

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

    public function draw()
    {
        return Html::tag('li', $this->text . $this->compile(), $this->makeAttributes());
    }

}
