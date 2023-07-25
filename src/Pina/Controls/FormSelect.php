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

    protected function drawInput()
    {
        return Html::tag('select', $this->drawOptions(), $this->makeInputOptions());
    }

    protected function drawOptions()
    {
        $options = '';

        if ($this->placeholder) {
            $options .= Html::tag('option', $this->placeholder, ['value' => '']);
        }

        foreach ($this->variants as $variant) {
            $title = isset($variant['title']) ? $variant['title'] : '';
            $id = isset($variant['id']) ? $variant['id'] : $title;
            $selected = is_array($this->value) ? in_array($id, $this->value) : !is_null($this->value) && $this->value == $id;
            $options .= Html::tag('option', $title, ['value' => $id, 'selected' => $selected ? 'selected' : null]);
        }

        return $options;
    }

    protected function makeInputOptions()
    {
        $options = ['multiple' => $this->multiple, 'class' => 'form-control'];
        if ($this->name) {
            $options['name'] = $this->name . ($this->multiple ? '[]' : '');
        }
        return array_filter($options);
    }

}
