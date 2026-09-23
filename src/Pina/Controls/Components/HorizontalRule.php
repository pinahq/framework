<?php


namespace Pina\Controls\Components;

use Pina\Controls\Control;

class HorizontalRule extends Control
{

    protected function draw(): string
    {
        return '<hr />';
    }

}