<?php

namespace Pina\Router;

interface DispatcherInterface
{
    public function dispatch(string $resource): ?string;

}