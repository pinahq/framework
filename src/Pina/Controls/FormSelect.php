<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Поле ввода с выбором из выпадающего списка
 * @package Pina\Controls
 */
class FormSelect extends FormInput
{

    protected $variants = [];
    protected $multiple = false;

    /**
     * @param array $list
     * @return $this
     */
    public function setVariants($list)
    {
        $this->variants = $list;
        return $this;
    }

    /**
     * @param boolean $multiple
     * @return $this
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function drawControl()
    {
        $options = '';
        foreach ($this->variants as $variant) {
            $title = isset($variant['title']) ? $variant['title'] : '';
            $id = isset($variant['id']) ? $variant['id'] : $title;
            $options .= Html::tag('option', $title, ['value' => $id, 'selected' => $this->value == $id ? 'selected' : null]);
        }

        return Html::tag('select', $options, array_filter(['name' => $this->name . ($this->multiple ? '[]' : ''), 'value' => $this->value, 'multiple' => $this->multiple, 'class' => 'form-control']));
    }

}
