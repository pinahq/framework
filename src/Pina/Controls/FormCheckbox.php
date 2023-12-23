<?php

namespace Pina\Controls;

use Pina\Html;

class FormCheckbox extends FormInput
{
    protected $type = 'checkbox';
    protected $checkedValue = 'Y';

    public function setOptionValue(string $value): FormCheckbox
    {
        $this->checkedValue = $value;
        return $this;
    }

    protected function drawInner()
    {
        $r = $this->drawInput();
        return $r;
    }

    protected function drawInput()
    {
        $options = ['type' => $this->type, 'value' => $this->checkedValue];

        if ($this->name) {
            $options['name'] = $this->name;
            $options['id'] = $this->name;
        }

        if ($this->value == $this->checkedValue) {
            $options['checked'] = 'checked';
        }

        $r = Html::tag('input', '', $options);
        $r .= Html::tag('label', $this->compact ? '' : $this->title, ['for' => $this->name]);
        return $r;
    }

}
