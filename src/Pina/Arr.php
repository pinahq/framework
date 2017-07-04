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

    public static function group($data, $key)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = $d[$key];
                $result[$k][] = $d;
            }
        }

        return $result;
    }

    public static function groupUnique($data, $key)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $d) {
                $k = $d[$key];
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
