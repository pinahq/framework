<?php

namespace Pina;

class Config
{
    protected static $path = false;
    protected static $data = [];

    /** @var ConfigInterface[] */
    protected static $registry = [];

    public static function init($path)
    {
        if (!empty(static::$path)) {
            return;
        }

        static::$path = $path;
    }

    public static function register(ConfigInterface $config)
    {
        static::$registry[] = $config;
    }

    public static function get($s, $key = null)
    {
        if (empty(static::$path)) {
            return null;
        }

        foreach (static::$registry as $config) {
            $value = $config->get($s, $key);
            if (!is_null($value)) {
                return $value;
            }
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