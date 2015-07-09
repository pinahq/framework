<?php

namespace Pina;

class Access
{

    private static $data = array();
    private static $config = false;
    
    const ACCESS_FIELD_PREG = 0;
    const ACCESS_FIELD_MAP = 1;
    const ACCESS_FIELD_ACTIONS = 2;
    const ACCESS_FIELD_GROUPS = 3;

    public static function permit($pattern, $actions, $groups = array())
    {
        if (!is_array($groups)) {
            $groups = explode(',', $groups);
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
            if (preg_match("/^" . $line[self::ACCESS_FIELD_PREG] . "$/si", $resource, $matches)) {
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

    public static function isPermitted($resource, $action, $group)
    {
        $resource = Url::trim($resource);
        foreach (self::$data as $line) {
            if (preg_match("/^" . $line[self::ACCESS_FIELD_PREG] . "$/si", $resource, $matches)) {
                $groupMatched = false;
                if (in_array($group, $line[self::ACCESS_FIELD_GROUPS])) {
                    $groupMatched = true;
                } elseif (in_array('self', $line[self::ACCESS_FIELD_GROUPS]) && in_array('user_id', $line[self::ACCESS_FIELD_MAP])) {
                    unset($matches[0]);
                    $matches = array_values($matches);
                    $params = array_combine($line[self::ACCESS_FIELD_MAP], $matches);
                    if (!empty($params['user_id'])) {
                        if (empty(self::$config)) {
                            self::$config = Config::load('access');
                        }
                        if (!empty(self::$config['auth'])) {
                            $cl = self::$config['auth'];
                            $cl::init();
                            if ($cl::userId() == $params['user_id'])
                            {
                                $groupMatched = true;
                            }
                        }
                    }
                }

                if ($groupMatched) {
                    if ($line[self::ACCESS_FIELD_ACTIONS] == "*") {
                        return true;
                    }
                    if (is_array($line[self::ACCESS_FIELD_ACTIONS]) && in_array($action, $line[self::ACCESS_FIELD_ACTIONS])) {
                        return true;
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
        
        if (empty(self::$config)) {
            self::$config = Config::load('access');
        }

        $group = '';
        if (!empty(self::$config['auth'])) {
            $cl = self::$config['auth'];
            $cl::init();

            if (!$cl::check()) {
                return false;
            }
            $group = $cl::group();
        }

        return Access::isPermitted($resource, $action, $group);
    }

}
