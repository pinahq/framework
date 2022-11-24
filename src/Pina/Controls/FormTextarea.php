<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Поле ввода многострочного текста
 * @package Pina\Controls
 */
class FormTextarea extends FormInput
{

    /** @var integer */
    protected $rows = 3;

    /**
     * Указать количество строк в поле ввода
     * @param integer $rows
     * @return $this
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    protected function drawInput()
    {
        return Html::tag('textarea', $this->value, array_filter(['name' => $this->name, 'class' => 'form-control', 'rows' => $this->rows]));
    }

}
