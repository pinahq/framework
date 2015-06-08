<?php

namespace Pina;

class Middleware
{

    private static $routes = array();
    private static $before = array();
    private static $after = array();
    private static $finalize = array();

    public static function bind($resource, $target)
    {
        list($preg, $map) = Url::preg($resource);
        self::$routes[] = array($preg, $target);
    }

    public static function shedule($mode, $resource, $actions, $middleware)
    {
        $actions = $actions == "*" ? $actions : explode(",", $actions);

        if ($actions != '*') {
            $method = Core::getRequestMethod();
            $found = false;
            foreach ($actions as $action) {
                if (Url::method($action) == $method) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return;
            }
        }


        list($preg, $map) = Url::preg($resource);

        $line = array();
        $line['preg'] = $preg;
        $line['map'] = $map;
        $line["actions"] = $actions;
        $line["middleware"] = $middleware;
        if ($mode == 'after') {
            self::$after[] = $line;
        } elseif ($mode == 'before') {
            self::$before[] = $line;
        } elseif ($mode == 'finalize') {
            self::$finalize[] = $line;
        }
    }

    public static function after($resource, $actions, $middleware)
    {
        self::shedule('after', $resource, $actions, $middleware);
    }

    public static function before($resource, $actions, $middleware)
    {
        self::shedule('before', $resource, $actions, $middleware);
    }
    
    public static function finalize($resource, $actions, $middleware)
    {
        self::shedule('finalize', $resource, $actions, $middleware);
    }

    public static function process($mode, $resource, $action, $data, $method, &$context)
    {
        $list = array();
        if ($mode == 'after') {
            $list = self::$after;
        } elseif ($mode == 'before') {
            $list = self::$before;
        } elseif ($mode == 'finalize') {
            $list = self::$finalize;
        }

        if (empty($list)) return;
        
        $resource = Url::trim($resource);
        $matches = false;
        foreach (self::$routes as $route) {
            list($preg, $target) = $route;
            if (preg_match("/^" . $preg . "$/si", $resource, $matches)) {
                $resource = $target;
                break;
            }
        }

        foreach ($list as $line) {
            if ($line['preg'] == '*' || preg_match("/^" . $line['preg'] . "$/si", $resource, $matches)) {

                if ($line["actions"] == "*" || (is_array($line['actions']) && in_array($action, $line['actions']))) {

                    if ($mode == 'finalize') {
                        $ps = array(&$context);
                        call_user_func_array($line['middleware'], $ps);
                    } else {
                        $result = Request::middleware($line['middleware'], $method, $data);
                    }
                }
            }
        }
    }

    public static function processBefore($resource, $action, $data, $method)
    {
        $c = false;
        self::process('before', $resource, $action, $data, $method, $c);
    }

    public static function processAfter($resource, $action, $data, $method)
    {
        $c = false;
        self::process('after', $resource, $action, $data, $method, $c);
    }
    
    public static function processFinalize($resource, $action, $data, $method, &$c)
    {
        self::process('finalize', $resource, $action, $data, $method, $c);
    }    

}