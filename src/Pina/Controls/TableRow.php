<?php

namespace Pina\Controls;

use Pina\Html;

class TableRow extends Control
{

    public function draw()
    {
        return Html::tag('tr', $this->compile());
    }

}
