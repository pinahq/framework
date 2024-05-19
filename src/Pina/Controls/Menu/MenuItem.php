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

        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();

        if (empty($inner)) {
            return '';
        }

        return Html::tag('li', $inner, $this->makeAttributes());
    }

    protected function isPermitted(): bool
    {
        return true;
    }

}