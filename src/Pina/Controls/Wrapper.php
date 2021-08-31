<?php


namespace Pina\Controls;


use Pina\Html;

class Wrapper extends Control
{
    protected $path = '';

    public function __construct($path)
    {
        $this->path = $path;
    }

    protected function draw()
    {
        return Html::nest($this->path, $this->compile(), $this->makeAttributes());
    }

}