<?php

namespace Pina;

class Route
{

    private static $routes = array();
    private static $owners = array();

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

    public static function bind($from, $to)
    {
        list($preg, $map) = Url::preg($from);
        self::$routes[$preg] = array($to, $map);
    }

    public static function route($resource, &$data)
    {
        $resource = Url::trim($resource);

        foreach (self::$routes as $k => $v) {
            if (preg_match("/^" . $k . "$/si", $resource, $matches)) {
                unset($matches[0]);
                $matches = array_values($matches);
                $params = array_combine($v[1], $matches);
                foreach ($params as $kk => $vv) {
                    $v[0] = trim(str_replace(':' . $kk . '/', $vv . '/', $v[0] . '/'), '/');
                }
                $data = Arr::merge($data, $params);
                return $v[0];
            }
        }
        return $resource;
    }

}
