<?php

namespace Pina\Controls;

use Pina\Html;

class FormTextarea extends FormInput
{

    /** @var integer */
    protected $rows = 3;

    /**
     * Указать количество строк в поле ввода
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function draw()
    {
        $r = Html::tag('label', $this->title, ['class' => 'control-label']);
        $r .= Html::tag('textarea', $this->value, array_filter(['name' => $this->name, 'class' => 'form-control', 'rows' => $this->rows]));
        $r .= $this->compile();
        return Html::tag('div', $r, $this->makeAttributes(['class' => 'form-group']));
    }

}
    