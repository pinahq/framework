<?php

namespace Pina;

class Config
{
    protected static $path = false;
    protected static $data = [];

    public static function init($path)
    {
        if (!empty(static::$path)) {
            return;
        }

        static::$path = $path;
    }
    
    public static function get($s, $key = null)
    {
        if (empty(static::$path)) {
            return null;
        }
        
        $data = static::load($s);
        
        if (empty($key)) {
            return $data;
        }

        return isset($data[$key]) ? $data[$key] : null;
    }

    public static function load($s)
    {
        if (empty(static::$path)) {
            return false;
        }
        
        if (isset(static::$data[$s])) {
            return static::$data[$s];
        }
        
        return static::$data[$s] = include static::$path . "/" . $s . ".php";
    }

}
