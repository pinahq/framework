<?php

namespace Pina\Controls;

use Pina\Html;

class Table extends Control
{

    public function draw()
    {
        return Html::tag('table', $this->compile(), ['class' => $this->makeClass()]);
    }

}
