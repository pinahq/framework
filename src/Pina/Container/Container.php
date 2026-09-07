<?php

namespace Pina\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    protected $definitions = [];
    protected $onMakeCallbacks = [];

    public function onMake($type, Callable $callable)
    {
        if (!isset($this->onMakeCallbacks[$type])) {
            $this->onMakeCallbacks[$type] = [];
        }
        $this->onMakeCallbacks[$type][] = $callable;
    }

    public function set($id, $concrete)
    {
        unset($this->definitions[$id]);
        $this->definitions[$id] = $concrete;
    }

    public function get(string $id)
    {
        if (isset($this->definitions[$id])) {
            if (is_object($this->definitions[$id])) {
                return $this->processOnMake(clone($this->definitions[$id]), $id);
            }

            if (is_string($this->definitions[$id])) {
                $className = $this->definitions[$id];
                if (class_exists($className)) {
                    return $this->processOnMake(new $className, $id);
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
            return $this->processOnMake(new $id, $id);
        }

        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function processOnMake($instance, $alias)
    {
        foreach ($this->onMakeCallbacks as $class => $fn) {
            if ($class === $alias || $instance instanceof $class) {
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
