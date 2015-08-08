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
        $controller = trim($controller, "/");
        if (!empty(self::$owners[$controller])) {
            return self::$owners[$controller];
        }
        
        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i+1));
            if (isset(self::$owners[$c])) {
                return self::$owners[$c];
            }
        }
        return false;
    }
    
    public static function context($key = null, $value = null)
    {
        if ($key === null) {
            return self::$context;
        }
        if ($value === null) {
            return isset(self::$content[$key])?self::$content[$key]:false;
        }
        self::$context[$key] = $value;
    }
    
    public static function resource($pattern, $parsed)
    {
        $parsed = Arr::merge(self::$context, $parsed);
        return Url::resource($pattern, $parsed);
    }

}
