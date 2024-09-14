<?php


namespace Pina\Controls\Nav;


use Pina\Controls\Control;

abstract class NavItem extends Control
{

    protected $title = '';

    protected function drawInner()
    {
        return $this->title;
    }

}