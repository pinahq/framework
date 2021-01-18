<?php

namespace Pina\Controls;

use Pina\Html;

class FormSelect extends FormInput
{

    protected $variants = [];
    protected $multiple = false;

    public function setVariants($list)
    {
        $this->variants = $list;
    }
    
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }
    
    public function draw()
    {
        $r = Html::tag('label', $this->title, ['class' => 'control-label']);
        
        $options = '';
        if ($this->placeholder) {
            $options .= Html::tag('option', $this->placeholder, ['value' => '', 'selected' => $this->value == '' ? 'selected' : null]);
        }
        
        foreach ($this->variants as $variant) {
            $title = $variant['title'] ?? '';
            $id = $variant['id'] ?? $title;
            $options .= Html::tag('option', $title, ['value' => $id, 'selected' => $this->value == $id ? 'selected' : null]);
        }
        
        $r .= Html::tag('select', $options, array_filter(['name' => $this->name . ($this->multiple ? '[]' : ''), 'value' => $this->value, 'multiple' => $this->multiple, 'class' => 'form-control']));
        $r .= $this->compile();
        return Html::tag('div', $r, ['class' => $this->makeClass('form-group')]);
    }

}
