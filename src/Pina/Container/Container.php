<?php

namespace Pina\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    protected $definitions = [];
    protected $sharedDefinitions = [];
    protected $shared = [];

    public function set($id, $concrete)
    {
        unset($this->definitions[$id]);
        unset($this->sharedDefinitions[$id]);
        unset($this->shared[$id]);

        $this->definitions[$id] = $concrete;
    }

    public function share($id, $concrete)
    {
        unset($this->definitions[$id]);
        unset($this->sharedDefinitions[$id]);
        unset($this->shared[$id]);

        $this->sharedDefinitions[$id] = $concrete;
    }

    public function get($id)
    {
        if (isset($this->shared[$id])) {
            return $this->shared[$id];
        }

        if (isset($this->sharedDefinitions[$id])) {

            if (is_object($this->sharedDefinitions[$id])) {
                return $this->sharedDefinitions[$id];
            }

            if (is_string($this->sharedDefinitions[$id])) {
                $className = $this->sharedDefinitions[$id];
                if (class_exists($className)) {
                    $this->shared[$id] = new $className;
                    return $this->shared[$id];
                }

                throw new NotFoundException(
                sprintf('Unable to create alias (%s) since class (%s) does not exists', $id, $className)
                );
            }

            throw new NotFoundException(
            sprintf('Unable to create alias (%s) as it does not have appropriate type', $id)
            );
        }

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

        throw new NotFoundException(
        sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function load($id)
    {
        if ($this->has($id)) {
            return $this->get($id);
        }

        if (class_exists($id)) {
            $inst = new $id;
            $this->share($id, $inst);
            return $inst;
        }

        throw new NotFoundException(
        sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function make($id)
    {
        if ($this->has($id)) {
            return $this->get($id);
        }

        if (class_exists($id)) {
            return new $id;
        }

        throw new NotFoundException(
        sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    public function has($id)
    {
        return array_key_exists($id, $this->definitions) || array_key_exists($id, $this->sharedDefinitions);
    }

}
