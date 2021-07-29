<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Обычный список без нумерации
 * @package Pina\Controls
 */
class UnorderedList extends Control
{

    protected function draw()
    {
        $compiled = $this->compile();
        return $compiled ? Html::tag('ul', $compiled, $this->makeAttributes()) : '';
    }

}
