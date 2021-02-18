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

    public function draw()
    {
        return Html::button(
            $this->title . $this->compile(),
            $this->makeAttributes(['class' => 'btn btn-' . $this->style, 'type' => 'submit'])
        );
    }

}
