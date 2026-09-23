<?php


namespace Pina\Controls\Form;

use Pina\Html;

class HiddenInput extends FormInput
{

    public function __construct()
    {
        $this->type = 'hidden';
    }

    protected function draw(): string
    {
        return $this->drawContent();
    }

    protected function drawContent(): string
    {
        $options = ['type' => $this->type, 'value' => $this->value];
        if ($this->name) {
            $options['name'] = $this->name;
        }
        return Html::tag('input', '', $this->makeAttributes($options));
    }
}