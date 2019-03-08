<?php

namespace Pina;

class Route
{

    private static $owners = array();
    private static $context = array();

    public static function own($controller, $module)
    {
        $controller = trim($controller, "/");
        self::$owners[$controller] = $module;
    }

    public static function owner($controller)
    {
        $c = self::base($controller);
        if ($c === null) {
            return false;
        }
        return self::$owners[$c];
    }
    
    public static function base($controller)
    {
        $controller = trim($controller, "/");
        if (!empty(self::$owners[$controller])) {
            return $controller;
        }
        
        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i+1));
            if (isset(self::$owners[$c])) {
                return $c;
            }
        }
        return null;
    }
    
    public static function context($key = null, $value = null)
    {
        if ($key === null) {
            return self::$context;
        }
        if ($value === null) {
            return isset(self::$context[$key])?self::$context[$key]:false;
        }
        self::$context[$key] = $value;
    }
    
    public static function resource($pattern, $parsed)
    {
        $parsed = Arr::merge(self::$context, $parsed);
        return Url::resource($pattern, $parsed, Request::resource());
    }

}
