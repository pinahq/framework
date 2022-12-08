<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Поле ввода без ввода :) с выводом текстовой информации вместо ввода
 * @package Pina\Controls
 */
class FormStatic extends FormInput
{

    protected function drawInput()
    {
        return Html::tag('p', $this->value, ['class' => 'form-control-static']);
    }

}
