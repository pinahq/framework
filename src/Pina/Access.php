<?php

namespace Pina;

class Access
{

    private static $data = array();
    private static $groups = array();
    private static $conditions = array();
    private static $sorted = false;

    const ACCESS_FIELD_PRIORITY = 0;
    const ACCESS_FIELD_PREG = 1;
    const ACCESS_FIELD_MAP = 2;
    const ACCESS_FIELD_GROUPS = 3;
    const ACCESS_FIELD_CONDITION_GROUP = 0;
    const ACCESS_FIELD_CONDITION = 1;

    public static function reset()
    {
        if (App::env() !== "test") {
            return;
        }

        self::$data = array();
        self::$groups = array();
        self::$conditions = array();
    }

    public static function permit($pattern, $groups = array())
    {
        if (!is_array($groups)) {
            $groups = array_filter(explode(';', $groups));
            foreach ($groups as $k => $g) {
                $groups[$k] = array_filter(explode(',', $g));
            }
        }

        list($preg, $map) = Url::preg($pattern);
        $controller = Url::controller($pattern);
        $priority = count(explode('/', $controller));
        $line = array(
            $priority,
            $preg,
            $map,
            $groups,
        );

        $found = false;
        foreach (self::$data as $k => $existed) {
            if ($existed[self::ACCESS_FIELD_PREG] == $line[self::ACCESS_FIELD_PREG]
                && $existed[self::ACCESS_FIELD_PRIORITY] == $line[self::ACCESS_FIELD_PRIORITY]) {
                self::$data[$k][self::ACCESS_FIELD_GROUPS] = array_merge(self::$data[$k][self::ACCESS_FIELD_GROUPS], $line[self::ACCESS_FIELD_GROUPS]);
                $found = true;
            }
        }

        if (!$found) {
            self::$data[] = $line;
        }
        self::$sorted = false;
    }

    public static function clear($pattern)
    {
        list($preg, $map) = Url::preg($pattern);
        foreach (self::$data as $k => $existed) {
            if ($existed[self::ACCESS_FIELD_PREG] == $preg) {
                unset(self::$data[$k]);
            }
        }
    }

    public static function isPrivate($resource)
    {
        $resource = Url::trim($resource);
        foreach (self::$data as $line) {
            $preg = $line[self::ACCESS_FIELD_PREG] . "\/";
            if (preg_match("/^" . $preg . "/si", $resource . "/", $matches)) {
                return true;
            }
        }
        return false;
    }

    public static function isPermitted($resource)
    {
        $resource = Url::trim($resource);
        if (!self::$sorted) {
            self::sort();
        }

        foreach (self::$data as $line) {
            $preg = $line[self::ACCESS_FIELD_PREG] . "\/";
            if (preg_match("/^" . $preg . "/si", $resource . "/", $matches)) {
                foreach ($line[self::ACCESS_FIELD_GROUPS] as $permittedGroups) {
                    $leftGroups = array_diff($permittedGroups, self::$groups);
                    if (count($leftGroups) === 0) {
                        return true;
                    }

                    if (!empty(self::$conditions)) {

                        $m = $matches;
                        unset($m[0]);
                        $params = array_combine($line[self::ACCESS_FIELD_MAP], array_values($m));

                        foreach (self::$conditions as $condition) {
                            $p = array_diff_assoc($condition[self::ACCESS_FIELD_CONDITION], $params);
                            if (!empty($p)) {
                                continue;
                            }

                            $leftGroups = array_diff($leftGroups, array($condition[self::ACCESS_FIELD_CONDITION_GROUP]));
                            if (count($leftGroups) === 0) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            }
        }
        return false;
    }

    private static function sort()
    {
        usort(self::$data, function($a, $b) {
            return $b[self::ACCESS_FIELD_PRIORITY] - $a[self::ACCESS_FIELD_PRIORITY];
        });
        self::$sorted = true;
    }

    public static function isHandlerPermitted($resource)
    {
        return Access::isPermitted($resource);
    }

    public static function addGroup($group)
    {
        if (empty($group)) {
            return;
        }

        self::$groups[] = $group;
    }

    public static function addCondition($group, $key, $value = null)
    {
        $condition = empty($value) ? $key : array($key => $value);
        self::$conditions[] = array($group, $condition);
    }

    public static function getGroups()
    {
        return self::$groups;
    }

    public static function getPermittedGroups($resource): array
    {
        $resource = Url::trim($resource);
        if (!self::$sorted) {
            self::sort();
        }

        foreach (self::$data as $line) {
            $preg = $line[self::ACCESS_FIELD_PREG] . "\/";
            if (preg_match("/^" . $preg . "/si", $resource . "/", $matches)) {
                $r = [];
                foreach ($line[self::ACCESS_FIELD_GROUPS] as $permittedGroups) {
                    $r[] = join($permittedGroups);
                }
                return $r;
            }
        }
        return [];
    }

    public static function hasGroup($group)
    {
        return in_array($group, self::$groups);
    }

}