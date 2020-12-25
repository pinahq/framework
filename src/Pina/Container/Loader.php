<?php

namespace Pina\Container;

use Psr\Container\ContainerInterface;

/**
 * Контейнер-загрузчик, в котором alias`ы - это имена классов,
 * которые продуцируются, если по ним не найдены связки в контейнере
 */
class Loader implements ContainerInterface
{

    protected $definitions = [];

    public function set($id, $concrete)
    {
        $this->definitions[$id] = $concrete;
    }

    public function get($id)
    {

        if (isset($this->definitions[$id])) {
            if (is_object($this->definitions[$id])) {
                return $this->definitions[$id];
            }

            if (is_string($this->definitions[$id])) {
                $className = $this->definitions[$id];
                if (class_exists($className)) {
                    $this->definitions[$id] = new $className;
                    return $this->definitions[$id];
                }

                throw new NotFoundException(
                sprintf('Unable to create alias (%s) since class (%s) does not exists', $id, $className)
                );
            }

            throw new NotFoundException(
            sprintf('Unable to create alias (%s) as it does not have appropriate type', $id)
            );
        }

        $className = $id;
        if (class_exists($className)) {
            $this->definitions[$id] = new $className;
            return $this->definitions[$id];
        }

        throw new NotFoundException(
        sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function has($id)
    {
        return array_key_exists($id, $this->definitions) || class_exists($id);
    }

}
