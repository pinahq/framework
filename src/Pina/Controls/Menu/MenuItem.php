<?php


namespace Pina\Controls\Menu;


use Pina\Controls\Control;
use Pina\Html;

class MenuItem extends Control
{
    protected function draw()
    {
        if (!$this->isPermitted()) {
            return '';
        }

        return Html::tag('li', $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(), $this->makeAttributes());
    }

    protected function isPermitted(): bool
    {
        return true;
    }

}