<?php

namespace Pina;

class Arr
{

    public static function merge($a1, $a2)
    {
        if (is_array($a1) && is_array($a2)) {
            return array_merge($a1, $a2);
        }
        if (is_array($a1) && !is_array($a2)) {
            return $a1;
        }

        return $a2;
    }

    public static function diff($a1, $a2)
    {
        $counts = [];
        foreach ($a2 as $v2) {
            if (isset($counts[$v2])) {
                $counts[$v2] ++;
            } else {
                $counts[$v2] = 1;
            }
        }

        foreach ($a1 as $k1 => $v1) {
            if (!empty($counts[$v1])) {
                unset($a1[$k1]);
                $counts[$v1] --;
            }
        }

        return array_values($a1);
    }

    public static function column(&$data, $name, $key = null)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                if (!isset($key)) {
                    $result [] = $d[$name];
                } else {
                    $result [$d[$key]] = $d[$name];
                }
            }
        }

        return $result;
    }

    public static function join($data, $join, $id, $name = '')
    {
        if (empty($data) || empty($join) || empty($id)) {
            return $data;
        }

        foreach ($data as $k => $v) {
            if (isset($v[$id]) && isset($join[$v[$id]])) {
                if ($name) {
                    $data[$k][$name] = $join[$v[$id]];
                } else {
                    $data[$k] = Arr::merge($data[$k], $join[$v[$id]]);
                }
            }
        }
        return $data;
    }

    public static function joinColumns($columns)
    {
        $table = [];
        foreach ($columns as $key => $column) {
            foreach ($column as $index => $item) {
                if (!isset($table[$index])) {
                    $table[$index] = [];
                }
                $table[$index][$key] = $item;
            }
        }
        return $table;
    }

    public static function group($data, $key, $default = '')
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = isset($d[$key]) ? $d[$key] : $default;
                $result[$k][] = $d;
            }
        }

        return $result;
    }

    public static function groupWithoutKey($data, $key, $default = '')
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = isset($d[$key]) ? $d[$key] : $default;
                unset($d[$key]);
                $result[$k][] = $d;
            }
        }

        return $result;
    }

    public static function groupUnique($data, $key, $default = '')
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = isset($d[$key]) ? $d[$key] : $default;
                $result[$k] = $d;
            }
        }

        return $result;
    }

    public static function groupColumn($data, $key, $column)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = $d[$key];
                unset($d[$key]);
                $result[$k][] = $d[$column];
            }
        }

        return $result;
    }

    public static function mine($subject, $a)
    {
        if (!is_array($a)) {
            return false;
        }
        if (empty($subject)) {
            return $a;
        }

        $l = strlen($subject);

        $r = array();
        foreach ($a as $k => $v) {
            if (strpos($k, $subject) === 0) {
                $r[trim(substr($k, $l), "_")] = $v;
            }
        }
        return $r;
    }

    public static function mineTreeValues($a)
    {
        $r = array();
        foreach ($a as $v) {
            if (is_array($v)) {
                $r = self::merge($r, static::mineTreeValues($v));
            } else {
                $r[] = $v;
            }
        }
        return $r;
    }

    public static function mineValues($subject, $a)
    {
        $a = self::mine($subject, $a);
        return static::mineTreeValues($a);
    }

    public static function mineSub($subject, $a)
    {
        if (!is_array($a)) {
            return false;
        }
        if (empty($subject)) {
            return $a;
        }

        $l = strlen($subject);

        $r = array();
        foreach ($a as $k => $v) {
            if (strpos($k, $subject) === 0) {
                $r[trim(substr($k, $l), "_")] = $v;
                unset($a[$k]);
            }
        }
        $a[trim($subject, "_")] = $r;
        return $a;
    }

    public static function get($array, $path, $default = null)
    {
        if (!is_numeric($path) && !is_string($path) && !is_array($path)) {
            return $default;
        }

        $path = is_array($path) ? $path : explode('.', $path);

        while (!is_null($segment = array_shift($path))) {
            if ($segment === '*') {
                if (!is_array($array)) {
                    return $default;
                }

                $result = static::getFromNextLevel($array, $path);

                return in_array('*', $path) ? static::collapse($result) : $result;
            }

            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    protected static function getFromNextLevel($array, $key)
    {
        $r = [];

        $key = is_string($key) ? explode('.', $key) : $key;

        foreach ($array as $item) {
            $r[] = static::get($item, $key);
        }

        return $r;
    }

    public static function collapse($a)
    {
        $r = [];

        foreach ($a as $v) {
            if (!is_array($v)) {
                continue;
            }

            $r = array_merge($r, $v);
        }

        return $r;
    }

    public static function set(&$array, $path, $value)
    {
        if (!is_numeric($path) && !is_string($path) && !is_array($path)) {
            return;
        }

        $keys = is_array($path) ? $path : explode('.', $path);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;
    }

    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (array_key_exists($key, $array)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    public static function has($array, $keys)
    {
        if (is_null($keys)) {
            return false;
        }

        $keys = (array) $keys;

        if (!$array) {
            return false;
        }

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (array_key_exists($key, $array)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (is_array($subKeyArray) && array_key_exists($segment, $subKeyArray)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }

}
