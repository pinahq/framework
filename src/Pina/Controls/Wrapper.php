<?php


namespace Pina\Controls;


use Pina\Html;

class Wrapper extends Control
{
    use ContainerTrait;

    protected $path = '';

    public function __construct($path)
    {
        $this->path = $path;
    }

    protected function draw()
    {
        return Html::nest(
            $this->path,
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        return '';
    }

}