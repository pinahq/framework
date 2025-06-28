<?php


namespace Pina\Controls;

use Pina\App;

class HandledForm extends Form
{

    protected $formClass = '';

    public function __construct()
    {
        $this->formClass = uniqid('fm');
        $this->addClass($this->formClass);
        $this->addClass('form pina-form');
    }

    /**
     * Получить уникальное имя класса тега формы, которое используется для javascript-обработчика
     * @return string
     */
    public function getFormClass()
    {
        return $this->formClass;
    }

}