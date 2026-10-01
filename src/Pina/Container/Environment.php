<?php

namespace Pina\Container;

use Psr\Container\ContainerInterface;

class Environment implements ContainerInterface
{

    protected ContainerInterface $container;
    protected ContainerInterface $parent;

    public function __construct(ContainerInterface $container, ContainerInterface $parent)
    {
        $this->container = $container;
        $this->parent = $parent;
    }

    public function getParent(): ContainerInterface
    {
        return $this->parent;
    }

    public function has(string $id)
    {
        return $this->container->has($id) || $this->parent->has($id);
    }

    public function get(string $id)
    {
        if ($this->container->has($id)) {
            return $this->container->get($id);
        }

        return $this->parent->get($id);
    }

    /** @todo в абстракции ContainerInterface этого метода нет, но он используется в устаревшем Control::setLayout */
    public function set($id, $concrete)
    {
        $this->container->set($id, $concrete);
    }

}