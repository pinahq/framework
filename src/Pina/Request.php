<?php

namespace Pina;

class Request
{

    protected static $stack = [];

    public static function internal($call)
    {
        self::push($call);
        $response = self::run();
        self::pop();
        if ($call->isolation() === false) {
            self::top()->mergePlaces($call);
        }

        return $response;
    }

    public static function top()
    {
        $top = count(self::$stack) - 1;
        if ($top < 0) {
            return null;
        }

        return self::$stack[$top];
    }

    public static function isInternalRequest()
    {
        return count(self::$stack) > 1;
    }

    public static function isExternalRequest()
    {
        return count(self::$stack) === 1;
    }

    public static function set($name, $value)
    {
        self::top()->set($name, $value);
    }

    public static function match($pattern)
    {
        $resource = Url::trim(self::top()->resource());

        if (empty($resource)) {
            return;
        }

        $pattern = Url::trim($pattern);
        $parsed = [];
        if (!Url::parse($resource, $pattern, $parsed)) {
            return false;
        }
        foreach ($parsed as $k => $v) {
            self::set($k, urldecode($v));
        }
        return true;
    }

    public static function exists($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return self::top()->exists($keys);
    }

    public static function has($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return self::top()->has($keys);
    }

    public static function all()
    {
        return self::top()->all();
    }

    public static function input($name, $default = null)
    {
        return self::top()->input($name, $default);
    }

    public static function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return self::top()->only($keys);
    }

    public static function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return self::top()->except($keys);
    }

    public static function intersect($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return self::top()->intersect($keys);
    }

    public static function raw()
    {
        return self::top()->raw();
    }

    public static function module()
    {
        return self::top()->module();
    }

    public static function resource()
    {
        $r = self::top();
        return $r ? $r->resource() : null;
    }

    public static function push($call)
    {
        array_push(self::$stack, $call);
    }

    public static function pop()
    {
        array_pop(self::$stack);
    }

    public static function run()
    {
        return self::top()->run();
    }

    public static function setLayout($layout)
    {
        self::top()->setLayout($layout);
    }

    public static function getLayout()
    {
        return self::top()->getLayout();
    }

    public static function setPlace($place, $content)
    {
        self::top()->setPlace($place, $content);
    }

    public static function getPlace($place)
    {
        $isolationLevel = 0;

        for ($i = count(self::$stack) - 1; $i > 0; $i --) {
            if (self::$stack[$i]->isolation()) {
                $isolationLevel = $i;
                break;
            }
        }

        if (!isset(self::$stack[$isolationLevel])) {
            return '';
        }

        return self::$stack[$isolationLevel]->getPlace($place);
    }

}
