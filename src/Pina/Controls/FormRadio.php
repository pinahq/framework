<?php


namespace App\Catalog\Controls;

use Pina\Controls\FormSelect;
use Pina\Html;

class FormRadio extends FormSelect
{

    protected function draw()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

    protected function drawInner()
    {
        $title = Html::nest('h4', $this->title);
        $r = $this->drawControl();
        return $title . Html::tag('div', $r, $this->makeAttributes([]));
    }

    public function drawInput()
    {
        $type = $this->multiple ? 'checkbox' : 'radio';

        $options = '';
        foreach ($this->variants as $variant) {
            $title = isset($variant['title']) ? $variant['title'] : '';
            $value = isset($variant['id']) ? $variant['id'] : $title;

            $checked = [];
            if ($this->value == $value) {
                $checked = ['checked' => 'checked'];
            }

            $tagId = implode('_', [$type, $this->name, $value]);

            $input = Html::input($type, $this->name . ($this->multiple ? '[]' : ''), $value, ['id' => $tagId] + $checked);
            $label = Html::tag('label', $title, ['for' => $tagId]);

            $options .= Html::nest('.form-group', $input . $label);
        }

        return $options;
    }
}