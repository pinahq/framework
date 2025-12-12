<?php

namespace Pina;

class Access
{

    protected $data = [];
    protected $groups = [];
    protected $conditions = [];
    protected $sorted = false;

    const ACCESS_FIELD_PRIORITY = 0;
    const ACCESS_FIELD_PREG = 1;
    const ACCESS_FIELD_MAP = 2;
    const ACCESS_FIELD_GROUPS = 3;
    const ACCESS_FIELD_CONDITION_GROUP = 0;
    const ACCESS_FIELD_CONDITION = 1;

    public function permit($pattern, $groups = array())
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
        foreach ($this->data as $k => $existed) {
            if ($existed[self::ACCESS_FIELD_PREG] == $line[self::ACCESS_FIELD_PREG]
                && $existed[self::ACCESS_FIELD_PRIORITY] == $line[self::ACCESS_FIELD_PRIORITY]) {
                $this->data[$k][self::ACCESS_FIELD_GROUPS] = array_merge($this->data[$k][self::ACCESS_FIELD_GROUPS], $line[self::ACCESS_FIELD_GROUPS]);
                $found = true;
            }
        }

        if (!$found) {
            $this->data[] = $line;
        }
        $this->sorted = false;
    }

    public function clear($pattern)
    {
        list($preg, $map) = Url::preg($pattern);
        foreach ($this->data as $k => $existed) {
            if ($existed[self::ACCESS_FIELD_PREG] == $preg) {
                unset($this->data[$k]);
            }
        }
    }

    public function isPrivate($resource)
    {
        $resource = Url::trim($resource);
        foreach ($this->data as $line) {
            $preg = $line[self::ACCESS_FIELD_PREG] . "\/";
            if (preg_match("/^" . $preg . "/si", $resource . "/", $matches)) {
                return true;
            }
        }
        return false;
    }

    public function isPermitted($resource)
    {
        $resource = Url::trim($resource);
        if (!$this->sorted) {
            self::sort();
        }

        foreach ($this->data as $line) {
            $preg = $line[self::ACCESS_FIELD_PREG] . "\/";
            if (preg_match("/^" . $preg . "/si", $resource . "/", $matches)) {
                foreach ($line[self::ACCESS_FIELD_GROUPS] as $permittedGroups) {
                    $leftGroups = array_diff($permittedGroups, $this->groups);
                    if (count($leftGroups) === 0) {
                        return true;
                    }

                    if (!empty($this->conditions)) {

                        $m = $matches;
                        unset($m[0]);
                        $params = array_combine($line[self::ACCESS_FIELD_MAP], array_values($m));

                        foreach ($this->conditions as $condition) {
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

    protected function sort()
    {
        usort($this->data, function($a, $b) {
            return $b[self::ACCESS_FIELD_PRIORITY] - $a[self::ACCESS_FIELD_PRIORITY];
        });
        $this->sorted = true;
    }

    public function isHandlerPermitted($resource)
    {
        return $this->isPermitted($resource);
    }

    public function addGroup($group)
    {
        if (empty($group)) {
            return;
        }

        $this->groups[] = $group;
    }

    public function addCondition($group, $key, $value = null)
    {
        $condition = empty($value) ? $key : array($key => $value);
        $this->conditions[] = array($group, $condition);
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getPermittedGroups($resource): array
    {
        $resource = Url::trim($resource);
        if (!$this->sorted) {
            self::sort();
        }

        foreach ($this->data as $line) {
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

    public function hasGroup($group)
    {
        return in_array($group, $this->groups);
    }

}