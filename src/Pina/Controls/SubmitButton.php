<?php

namespace Pina\Controls;

use Pina\Html;

class SubmitButton extends Button
{

    public function draw()
    {
        return Html::button($this->title . $this->compile(), ['class' => 'btn btn-primary', 'type' => 'submit']);
    }

}
