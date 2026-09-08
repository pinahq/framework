<?php

namespace Pina\Router;

use Pina\App;
use Psr\Container\ContainerInterface;

class RouteLocator
{
    protected Route $route;
    protected ContainerInterface $env;

    public function __construct(Route $route, ContainerInterface $env)
    {
        $this->route = $route;
        $this->env = $env;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function callRoute($action, $params)
    {
        $r = null;
        App::call($this->env, function() use ($action, $params, &$r) {
            $r = $this->route->call($action, $this->resolveParams($params, $this->route->getController()));
        });
        return $r;
    }

    public function runRoute($action, $params)
    {
        App::call($this->env, function() use ($action, $params) {
            $this->route->run($action, $this->resolveParams($params, $this->route->getController()));
        });
    }

    protected function resolveParams($params, $c)
    {
        $parts = count(explode('/', $c));
        $offset = $parts - 1;
        return array_slice(array_reverse(array_values($params)), $offset);
    }

}