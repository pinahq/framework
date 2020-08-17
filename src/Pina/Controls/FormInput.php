<?php

namespace Pina\Controls;

use Pina\Html;

class FormInput extends Control
{

    protected $title = '';
    protected $name = '';
    protected $value = '';

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

    public function draw()
    {
        $r = Html::tag('label', $this->title);
        $r .= Html::tag('input', '', array_filter(['name' => $this->name, 'value' => $this->value]));
        return $r;
    }

}
