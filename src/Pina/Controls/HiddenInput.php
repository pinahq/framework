<?php


namespace Pina\Controls;

use Pina\Html;

class HiddenInput extends FormInput
{

    public function __construct()
    {
        $this->type = 'hidden';
    }

    protected function draw()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

    protected function drawInner()
    {
        $options = ['type' => $this->type, 'value' => $this->value];
        if ($this->name) {
            $options['name'] = $this->name;
        }
        return Html::tag('input', '', $this->makeAttributes($options));
    }
}