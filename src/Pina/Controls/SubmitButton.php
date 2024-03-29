<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Кнопка отправки формы
 * @package Pina\Controls
 */
class SubmitButton extends Button
{

    protected $style = 'primary';

    protected function draw()
    {
        return Html::button(
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes(['class' => 'btn btn-' . $this->style, 'type' => 'submit'])
        );
    }

}
