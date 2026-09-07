<?php

namespace Pina\Router;

use Pina\App;
use Pina\Container\Container;
use Pina\Url;

class RouteGroup
{
    /** @var \Pina\Router\Route[]  */
    protected $items = [];


    /** @var RouteGroup[]  */
    protected $groups = [];

    protected $container;

    public function __construct()
    {
        $this->container = new Container();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function set($id, $concrete)
    {
        $this->container->set($id, $concrete);
    }


    /**
     * @param string $pattern
     * @param string $class
     */
    public function register($pattern, $class): \Pina\Router\Route
    {
        $route = new \Pina\Router\Route($pattern, $class);
        $this->items[$route->getController()] = $route;
        return $route;
    }

    public function isPermitted($resource)
    {
        return App::access()->isHandlerPermitted($resource);
    }

    public function makeGroup(): RouteGroup
    {
        $group = new RouteGroup();
        $this->groups[] = $group;
        return $group;
    }

    public function addGroup(RouteGroup $group)
    {
        $this->groups[] = $group;
    }

    public function getPatterns()
    {
        $patterns = [];
        foreach ($this->items as $item) {
            $patterns[] = $item->getPattern();
        }
        foreach ($this->groups as $group) {
            $patterns = array_merge($patterns, $group->getPatterns());
        }
        return $patterns;
    }


    public function locateRoute($controller): ?RouteLocator
    {
        $controller = trim($controller, "/");
        if ($locator = $this->findRegistered($controller)) {
            return $locator;
        }

        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i + 1));
            if ($locator = $this->findRegistered($c)) {
                return $locator;
            }
        }
        return null;
    }

    protected function findRegistered($controller): ?RouteLocator
    {
        if (isset($this->items[$controller])) {
            return new RouteLocator($this->items[$controller], $this);
        }

        foreach ($this->groups as $group) {
            if ($locator = $group->findRegistered($controller)) {
                return $locator;
            }
        }
        return null;
    }

    /**
     * @param string $controller
     * @return string|null
     */
    public function base($controller)
    {
        $controller = trim($controller, "/");
        if (!empty($this->items[$controller])) {
            return $controller;
        }

        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i + 1));
            if (isset($this->items[$c])) {
                return $c;
            }
        }
        return null;
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @param array $parsed
     * @return bool
     */
    public function parse($resource, $pattern, &$parsed)
    {
        list($preg, $map) = Url::preg($pattern);
        return $this->pregParse($resource, $preg, $map, $parsed);
    }

    /**
     * @param string $resource
     * @param string $preg
     * @param array $map
     * @param array $parsed
     * @return bool
     */
    public function pregParse($resource, $preg, $map, &$parsed)
    {
        $parsed = [];
        if (preg_match("/^" . $preg . "/si", $resource, $matches)) {
            unset($matches[0]);
            $matches = array_values($matches);
            $parsed = array_combine($map, $matches);
            return true;
        }
        return false;
    }
}