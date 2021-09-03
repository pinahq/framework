<?php

namespace Pina\Controls;

use Pina\CSRF;

/**
 * Поле с CSRF-токеном
 * @package Pina\Controls
 */
class CSRFHidden extends Control
{

    protected $method = '';

    /**
     * Указать HTTP-метод, которым будет передаваться CSRF-токен
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    protected function draw()
    {
        return $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
    }

    protected function drawInner()
    {
        return CSRF::formField($this->method);
    }

}
