<?php

namespace Pina\Place;

class PlaceComposer
{
    protected $key = '';
    protected $stack = [];

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function concrete(array $params): Place
    {
        return new Place($this, $params);
    }

    public function push(callable $callable)
    {
        $this->stack[] = $callable;
    }

    public function set(string $content)
    {
        $this->stack = [];
        $this->stack[] = new PlaceContent($content);
    }

    public function make(array $params)
    {
        $r = '';
        foreach ($this->stack as $callable) {
            $r .= call_user_func_array($callable, $params);
        }
        return $r;
    }
}