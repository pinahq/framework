<?php

namespace Pina\Controls;

use Pina\Html;

class Card extends Control
{

    public function draw()
    {
        return Html::tag('div', Html::tag('div', $this->compile(), ['class' => 'card-body']), ['class' => $this->makeClass('card')]);
    }

}
