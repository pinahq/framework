<?php

namespace Pina\Controls;

use Pina\Html;

class UnorderedList extends Control
{

    public function draw()
    {
        $compiled = $this->compile();
        return $compiled ? Html::tag('ul', $compiled, $this->makeAttributes()) : '';
    }

}
