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

    public static function column(&$data, $name, $key = false)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                if (empty($key)) {
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

    public static function rearrange($data, $key, $unique = false)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = $d[$key];
                unset($d[$key]);
                if ($unique) {
                    $result[$k] = $d;
                } else {
                    $result[$k][] = $d;
                }
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
                $r = self::merge($r, mineTreeValues($v));
            } else {
                $r[] = $v;
            }
        }
        return $r;
    }

    public static function mineValues($subject, $a)
    {
        $a = self::mine($subject, $a);
        return mineTreeValues($a);
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

}