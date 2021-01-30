<?php

namespace Pina\Controls;

use Pina\Html;

class FormStatic extends Control
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
        $r = Html::tag('label', $this->title, ['class' => 'control-label']);
        $r .= Html::tag('p', $this->value . $this->compile(), ['class' => 'form-control-static']);
        return Html::tag('div', $r, ['class' => $this->makeClass('form-group')]);
    }

}
