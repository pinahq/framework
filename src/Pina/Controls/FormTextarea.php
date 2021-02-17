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

    public function drawControl()
    {
        return Html::tag('textarea', $this->value, array_filter(['name' => $this->name, 'class' => 'form-control', 'rows' => $this->rows]));
    }

}
