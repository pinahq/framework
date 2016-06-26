<?php

namespace Pina;

//ресурс(resource) /menus, /menus/1, /menus/1/items
//метод (method): get, post, put, delete
//
//шаблон ресурса (pattern): /menus/:menu_id
//регулярка с шаблоном ресурса (preg): /menus/(.*)
//позиции отображения (map): array(1 => menu_id, 2 => menu_item_id)
//данные извлеченные из ресурса (parsed): array("id" => 5);
//
//контроллер(controller): /menus, /menus/items
//действие (action): show, index, update, store, create, destroy
//обработчик (handler): контроллер + действие = /menus/items/store
//Операции:
//controller: resource => controller
//route: resource + method => controller + action
//handler: controller + action => handler
//
//parse: resource + pattern => parsed
//preg: pattern => preg + map
//pregParse: resource + preg + map => parsed
//
//resource: parsed + pattern => resource

class Url
{

    public static function trim($resource)
    {
        if (empty($resource)) {
            return '';
        }
        $info = pathinfo($resource);
        $resource = trim(trim($info['dirname'],  '\/.') . '/' . $info['filename'], '\/');
        return $resource;
    }

    static public function controller($resource)
    {
        $resource = self::trim($resource);
        $parts = explode("/", $resource);
        $cnt = count($parts);
        $r = array();
        $i = 0;
        while ($i < $cnt) {
            if ($i == $cnt - 1 && in_array($parts[$i], array('index', 'block'))) {
                break;
            }

            $r[] = $parts[$i];
            $i++;
            if ($i == $cnt - 1 && in_array($parts[$i], array('create'))) {
                break;
            }
            $i++;
        }

        return implode('/', $r);
    }

    static public function method($action)
    {
        switch ($action) {
            case 'index': case 'show': return 'get';
            case 'update': return 'put';
            case 'destroy': return 'delete';
            case 'store': return 'post';
        }
        return false;
    }

    static public function route($resource, $method)
    {
        $resource = self::trim($resource);
        
        $parts = explode("/", $resource);
        $cnt = count($parts);
        $r = array();
        $d = array();
        $a = '';
        $i = 0;
        while ($i < $cnt) {
            if ($i == $cnt - 1 && in_array($parts[$i], array('index', 'block'))) {
                $a = $parts[$i];
                break;
            }

            $r[] = $parts[$i];
            $i++;
            if ($i == $cnt - 1 && in_array($parts[$i], array('create', 'block'))) {
                $a = $parts[$i];
                break;
            }
            if ($i >= $cnt) {
                break;
            }
            $d[] = $parts[$i];
            $i++;
        }
        
        $isCollection = $i % 2;
        if (empty($a)) {
            switch ($method) {
                case 'get': $a = $isCollection ? 'index' : 'show';
                    break;
                case 'put': $a = 'update';
                    break;
                case 'delete': $a = 'destroy';
                    break;
                case 'post': $a = 'store';
                    break;
            }
        }

        if (empty($a)) {
            $a = 'index';
        }

        $data = array();
        $k = $isCollection ? 'pid' : 'id';
        while (count($d)) {
            $data[$k] = array_pop($d);
            $k = 'p' . $k;
        }

        return array(join("/", $r), $a, $data);
    }

    static public function handler($controller, $action)
    {
        $controller = trim($controller, "/");
        $owner = Route::owner($controller);
        if (empty($owner)) {
            return false;
        }
        $path = ModuleRegistry::getPath($owner);
        return $path . '/' . App::get() . '/' . $controller . '/' . $action;
    }

    static public function module($controller)
    {
        $controller = trim($controller, "/");
        return Route::owner($controller);
    }

    static public function parse($resource, $pattern)
    {
        list($preg, $map) = self::preg($pattern);
        return self::pregParse($resource, $preg, $map);
    }

    static public function preg($pattern)
    {
        $pattern = trim($pattern, "/");
        $pattern .= '/';
        preg_match_all('/:[^\/]*\/?/si', $pattern, $map);

        $preg = str_replace('/', '\/', $pattern);
        $map = array_pop($map);
        foreach ($map as $k => $m) {
            $map[$k] = $m = trim($m, ":/");
            $preg = str_replace(':' . $m . '\/', "([^\/]*)" . '\/', $preg);
        }
        $preg = substr($preg, 0, strlen($preg) - 2);
        return array($preg, $map);
    }

    static public function pregParse($resource, $preg, $map)
    {
        $parsed = array();
        if (preg_match("/^" . $preg . "$/si", $resource, $matches)) {
            unset($matches[0]);
            $matches = array_values($matches);
            $parsed = array_combine($map, $matches);
        }
        return $parsed;
    }

    static public function resource($pattern, $parsed)
    {
        $resource = $pattern;
        foreach ($parsed as $k => $v) {
            if (empty($v)) {
                $v = 0;
            }
            $resource = trim(str_replace(':' . $k . '/', $v . '/', $resource . '/'), '/');
        }
        return $resource;
    }

}