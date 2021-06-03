<?php


namespace Pina\Controls;

use Pina\Html;

class HiddenInput extends FormInput
{

    public function __construct()
    {
        $this->type = 'hidden';
    }

    public function draw()
    {
        $options = ['type' => $this->type, 'value' => $this->value];
        if ($this->name) {
            $options['name'] = $this->name;
        }
        return Html::tag('input', '', $this->makeAttributes($options));
    }
}