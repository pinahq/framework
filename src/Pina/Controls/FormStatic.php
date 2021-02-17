<?php

namespace Pina\Controls;

use Pina\Html;

class FormStatic extends FormInput
{

    public function drawControl()
    {
        return Html::tag('p', $this->value . $this->compile(), ['class' => 'form-control-static']);
    }

}
