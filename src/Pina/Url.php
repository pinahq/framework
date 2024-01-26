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
        $resource = trim(trim($info['dirname'], '\/.') . '/' . $info['filename'], '\/');
        return $resource;
    }

    public static function controller($resource)
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

    public static function method($action)
    {
        switch ($action) {
            case 'index':
            case 'show':
                return 'get';
            case 'update':
                return 'put';
            case 'destroy':
                return 'delete';
            case 'store':
                return 'post';
        }
        return false;
    }

    public static function route($resource, $method)
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
                case 'get':
                    $a = $isCollection ? 'index' : 'show';
                    break;
                case 'put':
                    $a = 'update';
                    break;
                case 'delete':
                    $a = 'destroy';
                    break;
                case 'post':
                    $a = 'store';
                    break;
                case 'options':
                    $a = 'options';
                    break;
                case 'title':
                    $a = 'title';
                    break;
            }
        }

        if (empty($a)) {
            $a = 'index';
        }

        $data = array();
        $k = $isCollection ? 'pid' : 'id';
        if ($isCollection && in_array($a, ['update', 'destroy', 'store', 'options', 'title'])) {
            $data['id'] = null;
        }
        while (count($d)) {
            $data[$k] = array_pop($d);
            $k = 'p' . $k;
        }

        return array(join("/", $r), $a, $data);
    }

    public static function handler($controller, $action)
    {
        return 'http/' . trim($controller, "/") . '/' . $action;
    }

    public static function parse($resource, $pattern, &$parsed)
    {
        list($preg, $map) = self::preg($pattern);
        return self::pregParse($resource, $preg, $map, $parsed);
    }

    public static function preg($pattern)
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

    public static function pregParse($resource, $preg, $map, &$parsed)
    {
        $parsed = [];
        if (preg_match("/^" . $preg . "$/si", $resource, $matches)) {
            unset($matches[0]);
            $matches = array_values($matches);
            $parsed = array_combine($map, $matches);
            return true;
        }
        return false;
    }

    public static function resource($pattern, $parsed, $parent = '')
    {
        $resource = $pattern;
        foreach ($parsed as $k => $v) {
            if (empty($v)) {
                $v = 0;
            }
            $resource = trim(str_replace(':' . $k . '/', (is_array($v) ? '' : $v) . '/', $resource . '/'), '/');
        }
        $level = 0;
        while (isset($resource[$level]) && in_array($resource[$level], ['$', '@'])) {
            $level++;
        }
        if ($level > 0) {
            $parentResource = explode('/', rtrim($parent, '/'));
            for ($i = 0; $i < $level - 1; $i++) {
                array_pop($parentResource);
            }
            $resource = implode('/', $parentResource) . substr($resource, $level);
        }
        return $resource;
    }

    /**
     * Проверяет, является ли $nested вложенным в $base URL, чтобы понять, выделить ли $base, как активный пункт или нет
     * Возвращает вес сходства или 0, если сходтсво отсутствует.
     * Если в меню присутсвует несколько общих ссылок, то активной стоит пометить ту, у которой выше вес сходства
     * @param string $base
     * @param string $nested
     * @return int
     */
    public static function nestedWeight(string $base, string $nested)
    {
        $parsedBase = parse_url($base);
        parse_str($parsedBase['query'] ?? '', $baseQuery);

        $parsedNested = parse_url($nested);
        parse_str($parsedNested['query'] ?? '', $nestedQuery);

        if (!empty($parsedNested['host']) && !empty($parsedBase['host']) && $parsedNested['host'] != $parsedBase['host']) {
            return 0;
        }

        if ($parsedNested['path'] != $parsedBase['path']) {
            return 0;
        }

        $intersected = array_intersect($baseQuery, $nestedQuery);
        if (count($intersected) < count($baseQuery)) {
            return 0;
        }

        return count($intersected) + 1;
    }

}