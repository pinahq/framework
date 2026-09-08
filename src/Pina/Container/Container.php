<?php

namespace Pina\Container;

class Container extends AbstractCallbackContainer
{
    protected $definitions = [];
    protected $onTypeCallbacks = [];

    public function onTypeGet($type, Callable $callable)
    {
        if (!isset($this->onTypeCallbacks[$type])) {
            $this->onTypeCallbacks[$type] = [];
        }
        $this->onTypeCallbacks[$type][] = $callable;
    }

    public function set($id, $concrete)
    {
        unset($this->definitions[$id]);
        $this->definitions[$id] = $concrete;
    }

    public function resolve(string $id)
    {
        if (isset($this->definitions[$id])) {
            if (is_object($this->definitions[$id])) {
                return clone($this->definitions[$id]);
            }

            if (is_string($this->definitions[$id])) {
                $className = $this->definitions[$id];
                if (class_exists($className)) {
                    return new $className;
                }

                throw new NotFoundException(
                    sprintf('Unable to create alias (%s) since class (%s) does not exists', $id, $className)
                );
            }

            throw new NotFoundException(
                sprintf('Unable to create alias (%s) as it does not have appropriate type', $id)
            );
        }

        if (class_exists($id)) {
            return new $id;
        }

        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function get(string $id)
    {
        $instance = parent::get($id);
        foreach ($this->onTypeCallbacks as $type => $fn) {
            if ($instance instanceof $type) {
                $fn($instance);
            }
        }
        return $instance;
    }

    public function has($id)
    {
        return array_key_exists($id, $this->definitions);
    }

    public function getKeys(): array
    {
        return array_unique(array_keys($this->definitions));
    }
}
