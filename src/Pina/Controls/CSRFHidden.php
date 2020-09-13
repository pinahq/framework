<?php

namespace Pina\Controls;

use Pina\Html;

class CSRFHidden extends Control
{

    protected $method = '';

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function draw()
    {
        return \Pina\CSRF::formField($this->method);
    }

}
