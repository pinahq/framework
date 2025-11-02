<?php

namespace Pina\Container;

use Psr\Container\ContainerInterface;

abstract class AbstractCallbackContainer implements ContainerInterface
{
    protected $onGetCallbacks = [];

    abstract protected function resolve(string $id);

    abstract public function has(string $id);

    public function onGet(string $id, Callable $callable)
    {
        if (!isset($this->onGetCallbacks[$id])) {
            $this->onGetCallbacks[$id] = [];
        }
        $this->onGetCallbacks[$id][] = $callable;
    }

    public function get(string $id)
    {
        $r = $this->resolve($id);
        if (isset($this->onGetCallbacks[$id])) {
            foreach ($this->onGetCallbacks[$id] as $fn) {
                $fn($r);
            }
        }
        return $r;
    }


}