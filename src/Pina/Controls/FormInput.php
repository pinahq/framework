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
    protected $description = '';
    protected $type = 'text';
    protected $placeholder = null;
    protected $required = false;

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

    public function setDescription($description)
    {
        $this->description = $description;
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

    public function setRequired(bool $required = true)
    {
        $this->required = $required;
        return $this;
    }

    protected function draw()
    {
        return Html::tag(
            'div',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes(['class' => 'form-group'])
        );
    }

    protected function drawInner()
    {
        $r = Html::tag('label', $this->getLabelContent(), ['class' => 'control-label']);
        $r .= $this->drawControl();
        return $r;
    }

    protected function getLabelContent()
    {
        return $this->title . ($this->required ? ' *' : '');
    }

    protected function drawControl()
    {
        return $this->drawInput() . $this->drawDescription();
    }

    protected function drawInput()
    {
        return Html::tag('input', '', $this->makeInputOptions());
    }

    protected function drawDescription()
    {
        if (empty($this->description)) {
            return '';
        }
        return Html::nest('span.help-block text-muted/small', $this->description);
    }

    protected function makeInputOptions()
    {
        $options = ['type' => $this->type, 'value' => $this->value, 'class' => 'form-control'];
        if ($this->name) {
            $options['name'] = $this->name;
        }
        return $options;
    }

}
