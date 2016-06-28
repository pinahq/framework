<?php

namespace Pina;

class DispatcherRegistry
{

    private static $dispatchers = array();

    public static function register($namespace)
    {
        self::$dispatchers[] = $namespace;
    }

    public static function dispatch($resource)
    {
        foreach (self::$dispatchers as $namespace) {
            $className = $namespace . '\\Dispatcher';
            if ($r = call_user_func(array($className, 'dispatch'), $resource)) {
                return $r;
            }
        }
        return $resource;
    }

}
