<?php

namespace Pina\Router;

use Pina\Router;

class RouteGroup
{
    /** @var Router */
    protected $router;
    protected $tags = [];

    public function __construct(Router $router, $tags = [])
    {
        $this->router = $router;
        $this->tags  = $tags;
    }

    public function register($pattern, $class, $context = []): \Pina\Router\Route
    {
        $route = $this->router->register($pattern, $class, $context);
        foreach ($this->tags as $tag) {
            $route->addTag($tag);
        }
        return $route;
    }
}