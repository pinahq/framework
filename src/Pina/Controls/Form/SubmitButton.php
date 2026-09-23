<?php

namespace Pina\Controls\Form;

use Pina\Controls\Components\Button;
use Pina\Html;

/**
 * Кнопка отправки формы
 */
class SubmitButton extends Button
{

    protected $style = 'primary';

    protected function draw(): string
    {
        return Html::button(
            $this->drawContent(),
            $this->makeAttributes(['class' => 'btn btn-' . $this->style, 'type' => 'submit'])
        );
    }

}
