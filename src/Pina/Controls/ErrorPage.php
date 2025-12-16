<?php

namespace Pina\Controls;

class ErrorPage extends Control
{
    protected $code;

    public function load(string $code)
    {
        $this->code = $code;
    }

    protected function draw()
    {
        return $this->resolveText();
    }

    protected function resolveText(): string
    {
        return $this->code;
    }

}