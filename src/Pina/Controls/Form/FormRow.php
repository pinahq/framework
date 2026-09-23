<?php

namespace Pina\Controls\Form;

use Pina\Controls\ControlContainer;
use Pina\Controls\Wrapper;
use Pina\Html;

class FormRow extends ControlContainer
{
    protected function draw(): string
    {
        return Html::nest('.row', $this->drawContent(), $this->makeAttributes());
    }

    protected function drawContent(): string
    {
        $r = '';
        foreach ($this->inner as $c) {
            $wrapper = new Wrapper('.col');
            $wrapper->append($c);
            $r .= $wrapper;
        }
        return $r;
    }
}