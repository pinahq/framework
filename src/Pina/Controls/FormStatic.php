<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Поле ввода без ввода :) с выводом текстовой информации вместо ввода
 * @package Pina\Controls
 */
class FormStatic extends FormInput
{

    public function drawControl()
    {
        return Html::tag('p', $this->value . $this->compile(), ['class' => 'form-control-static']);
    }

}
