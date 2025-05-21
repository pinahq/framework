<?php

namespace Pina\Place;

class Place
{

    protected $composer;
    protected $params = [];

    public function __construct(PlaceComposer $composer, array $params)
    {
        $this->composer = $composer;
        $this->params = $params;
    }

    public function push(callable $callable)
    {
        $this->composer->push($callable);
    }

    public function make(...$params): string
    {
        return $this->composer->make(!empty($params) ? $params : $this->params);
    }

    public function __toString()
    {
        return $this->make();
    }

}