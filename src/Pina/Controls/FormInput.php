<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Текстовое поле ввода
 * @package Pina\Controls
 */
class FormInput extends Control
{

    protected $title = '';
    protected $name = '';
    protected $value = '';
    protected $type = 'text';
    protected $placeholder = null;

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function draw()
    {
        $r = Html::tag('label', $this->title, ['class' => 'control-label']);
        $r .= $this->drawControl();
        $r .= $this->compile();
        return Html::tag('div', $r, $this->makeAttributes(['class' => 'form-group']));
    }

    protected function drawControl()
    {
        return Html::tag('input', '', array_filter(['type' => $this->type, 'name' => $this->name, 'value' => $this->value, 'class' => 'form-control']));
    }

}
