<?php

namespace Pina\Controls;

use Pina\Html;

class FormInput extends Control
{

    protected $title = '';
    protected $name = '';
    protected $value = '';
    protected $type = 'text';
    protected $placeholder = null;

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function draw()
    {
        $r = Html::tag('label', $this->title, ['class' => 'control-label']);
        $r .= Html::tag('input', '', array_filter(['type' => $this->type, 'name' => $this->name, 'value' => $this->value, 'class' => 'form-control']));
        $r .= $this->compile();
        return Html::tag('div', $r, ['class' => $this->makeClass('form-group')]);
    }

}
