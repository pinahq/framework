<?php


namespace Pina\Controls;


use Pina\Html;

class Wrapper extends ControlContainer
{

    protected $path = '';

    public function __construct($path)
    {
        $this->path = $path;
    }

    protected function draw(): string
    {
        return Html::nest($this->path, $this->drawContent(), $this->makeAttributes());
    }

}