<?php

namespace Pina\Container;

class SingletonContainer
{
    protected $definitions = [];
    protected $loaded = [];

    public function onLoad($id, Callable $callable)
    {
        //если объект уже проинициализирован, то считаем, что его инициализация была неполной и это ошибка
        if (isset($this->loaded[$id])) {
            throw new \Exception($id . ' initialization has not been completed');
        }

        if (!isset($this->onLoadCallbacks[$id])) {
            $this->onLoadCallbacks[$id] = [];
        }
        $this->onLoadCallbacks[$id][] = $callable;
    }


    public function set($id, $concrete)
    {
        if (isset($this->loaded[$id])) {
            throw new \Exception($id . ' initialization has not been completed');
        }

        unset($this->definitions[$id]);
        $this->definitions[$id] = $concrete;
    }

    public function load($id)
    {
        if ($this->has($id)) {
            return $this->get($id);
        }

        return $this->make($id, $id);
    }

    public function get(string $id)
    {
        if (isset($this->loaded[$id])) {
            return $this->loaded[$id];
        }

        if (isset($this->definitions[$id])) {
            if (is_object($this->definitions[$id])) {
                return $this->definitions[$id];
            }

            if (is_string($this->definitions[$id])) {
                return $this->make($this->definitions[$id], $id);
            }

            throw new NotFoundException(
                sprintf('Unable to create alias (%s) as it does not have appropriate type', $id)
            );
        }

        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function has($id)
    {
        return array_key_exists($id, $this->loaded) || array_key_exists($id, $this->definitions);
    }

    protected function make($className, $alias)
    {
        if (!class_exists($className)) {
            throw new NotFoundException(
                sprintf('Unable to create alias (%s) since class (%s) does not exists', $alias, $className)
            );
        }

        $inst = new $className;
        $this->loaded[$alias] = $inst;
        if (isset($this->onLoadCallbacks[$alias])) {
            foreach ($this->onLoadCallbacks[$alias] as $fn) {
                $fn($inst);
            }
        }
        return $inst;
    }

}