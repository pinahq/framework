<?php

namespace Pina\Controls;

use Pina\Html;
use Pina\Controls\Control;

class FormSelect extends FormInput
{

    protected $variants = [];
    protected $multiple = false;

    public function setVariants($list)
    {
        $this->variants = $list;
        return $this;
    }

    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function drawControl()
    {
        $options = '';
        foreach ($this->variants as $variant) {
            $title = $variant['title'] ?? '';
            $id = $variant['id'] ?? $title;
            $options .= Html::tag('option', $title, ['value' => $id, 'selected' => $this->value == $id ? 'selected' : null]);
        }

        return Html::tag('select', $options, array_filter(['name' => $this->name . ($this->multiple ? '[]' : ''), 'value' => $this->value, 'multiple' => $this->multiple, 'class' => 'form-control']));
    }

}
