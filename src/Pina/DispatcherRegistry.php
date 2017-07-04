<?php

namespace Pina;

class DispatcherRegistry
{

    private static $dispatchers = array();

    public static function register($dispatcher)
    {
        self::$dispatchers[] = $dispatcher;
    }

    public static function dispatch($resource)
    {
        foreach (self::$dispatchers as $dispatcher) {
            if ($r = $dispatcher->dispatch($resource)) {
                return $r;
            }
        }
        return $resource;
    }

}
