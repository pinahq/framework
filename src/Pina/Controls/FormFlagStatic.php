<?php


namespace Pina\Controls;

use Pina\Html;

class FormFlagStatic extends FormStatic
{

    protected function drawInput()
    {
        if ($this->value == 'Y') {
            return Html::nest('span.mdi mdi-check-circle text-success');
        }
        return Html::nest('span.mdi mdi-close-circle text-warning');
    }

}