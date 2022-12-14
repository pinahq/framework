<?php

namespace Pina\Controls;

use Pina\Html;

class FormCheckbox extends FormInput
{
    protected $checked = false;
    protected $type = 'checkbox';

    public function setChecked(bool $checked)
    {
        $this->checked = $checked;

        return $this;
    }

    protected function drawInner()
    {
        return $this->drawControl();
    }

    protected function drawInput()
    {
        $options = ['type' => $this->type, 'value' => $this->value, 'class' => 'check'];

        if ($this->name) {
            $options['name'] = $this->name;
            $options['id'] = $this->name;
        }

        if ($this->checked) {
            $options['checked'] = $this->checked;
        }

        $r = Html::tag('input', '', $options);
        $r .= Html::tag('label', $this->compact ? '' : $this->title, ['for' => $this->name]);
        return $r;
    }

}
