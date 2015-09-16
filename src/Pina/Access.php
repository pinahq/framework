<?php

namespace Pina;

class Access
{

    private static $data = array();
    private static $groups = array();
    private static $conditions = array();

    const ACCESS_FIELD_PREG = 0;
    const ACCESS_FIELD_MAP = 1;
    const ACCESS_FIELD_ACTIONS = 2;
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

    public static function permit($pattern, $actions, $groups = array())
    {
        if (!is_array($groups)) {
            $groups = explode(';', $groups);
            foreach ($groups as $k => $g) {
                $groups[$k] = explode(',', $g);
            }
        }

        list($preg, $map) = Url::preg($pattern);
        $line = array(
            $preg,
            $map,
            $actions == "*" ? $actions : explode(",", $actions),
            $groups
        );
        self::$data[] = $line;
    }

    public static function isPrivate($resource, $action)
    {
        $resource = Url::trim($resource);
        foreach (self::$data as $line) {
            $preg = $line[self::ACCESS_FIELD_PREG]."\/";
            if (preg_match("/^" . $preg . "/si", $resource."/", $matches)) {
                if ($line[self::ACCESS_FIELD_ACTIONS] == "*") {
                    return true;
                }
                if (is_array($line[self::ACCESS_FIELD_ACTIONS]) && in_array($action, $line[self::ACCESS_FIELD_ACTIONS])) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function isPermitted($resource, $action)
    {
        $resource = Url::trim($resource);
        foreach (self::$data as $line) {
            if ($line[self::ACCESS_FIELD_ACTIONS] !== "*" && !in_array($action, $line[self::ACCESS_FIELD_ACTIONS])) {
                continue;
            }

            $preg = $line[self::ACCESS_FIELD_PREG]."\/";
            if (preg_match("/^" . $preg. "/si", $resource."/", $matches)) {
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
            }
        }
        return false;
    }

    public static function isHandlerPermitted($resource, $action)
    {
        if (!Access::isPrivate($resource, $action)) {
            return true;
        }

        return Access::isPermitted($resource, $action);
    }

    public static function addGroup($group)
    {
        self::$groups[] = $group;
    }

    public static function addCondition($group, $key, $value = null)
    {
        $condition = empty($value)?$key:array($key => $value);
        self::$conditions[] = array($group, $condition);
    }

}
