<?php


namespace Pina\Controls\Nav;


use Pina\Controls\Components\Badge;
use Pina\Controls\Control;
use Pina\Html;

abstract class NavItem extends Control
{

    protected $title = '';

    protected ?Badge $badge = null;

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function setBadge(Badge $badge)
    {
        $this->badge = $badge;
    }

    protected function draw(): string
    {
        return Html::li($this->drawContent(), $this->makeAttributes());
    }

    protected function drawContent(): string
    {
        return ($this->badge ?? '') . $this->title;
    }

}