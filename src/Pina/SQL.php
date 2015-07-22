<?php

namespace Pina;

/*
 * TODO:
 * 1) добавить возможность использовать подзапросы в select/from/where
 * 2) для Join возможность использовать and/or в условии соединения
 */

class SQL
{

    public $db = '';
    private $select = array();
    private $from = '';
    private $joins = array();
    private $where = array();
    private $groupBy = array();
    private $having = array();
    private $orderBy = array();
    private $limitStart = 0;
    private $limitCount = 0;
    private $unions = array();

    public static function table($table, $db = false)
    {
        return new SQL($table, $db);
    }

    public function init()
    {
        $this->select = array();
        $this->joins = array();
        $this->where = array();
        $this->groupBy = array();
        $this->having = array();
        $this->orderBy = array();
        $this->limitStart = 0;
        $this->limitCount = 0;
        $this->unions = array();
        return $this;
    }

    protected function __construct($table, $db = false)
    {
        $this->db = $db ? $db : DB::get();
        $this->from = $table;
    }

    public function select($field)
    {
        $this->select[] = $field;
        return $this;
    }

    public function join($type, $table, $field, $table2 = false, $field2 = false)
    {
        if (!empty($table2) && !empty($field2)) {
            $this->joins[$type][$table] = array($field => array($table2 => $field2));
        } else if (is_array($field)) {
            $this->joins[$type][$table] = $field;
        }
        return $this;
    }

    public function leftJoin($table, $field, $table2 = false, $field2 = false)
    {
        return $this->join('left', $table, $field, $table2, $field2);
    }

    public function innerJoin($table, $field, $table2 = false, $field2 = false)
    {
        return $this->join('inner', $table, $field, $table2, $field2);
    }

    public function rightJoin($table, $field, $table2 = false, $field2 = false)
    {
        return $this->join('right', $table, $field, $table2, $field2);
    }

    public function where($condition)
    {
        $this->where[] = $condition;
        return $this;
    }

    public function whereBy($field, $needle)
    {
        return $this->where($this->getByCondition($field, $needle));
    }

    public function whereNotBy($field, $needle)
    {
        return $this->where($this->getByCondition($field, $needle, true));
    }

    public function groupBy($table, $field)
    {
        $this->groupBy[] = $table . '.' . $field;
        return $this;
    }

    public function having($having)
    {
        $this->having[] = $having;
        return $this;
    }

    public function union($sql)
    {
        $this->unions[] = $sql;
        return $this;
    }

    public function orderBy($orderBy)
    {
        if (empty($orderBy) || in_array($orderBy, $this->orderBy)) {
            return $this;
        }

        $this->orderBy[] = $orderBy;
        return $this;
    }

    public function limit($start, $count = null)
    {
        if ($count === null) {
            $this->limitCount = $start;
            $this->limitStart = null;
            return $this;
        }

        $this->limitStart = $start;
        $this->limitCount = $count;
        return $this;
    }

    public function paging(&$paging)
    {
        $paging->setTotal($this->count());

        $limitStart = intval($paging->getStart());
        $limitCount = intval($paging->getCount());
        $this->limit($limitStart, $limitCount);

        return $this;
    }

    protected function extractTableLink($table)
    {
        if (strpos($table, 'AS') !== false) {
            return substr($table, strpos($table, 'AS') + 3);
        }

        if (strpos($table, " ") > 0) {
            return strstr($table, " ");
        }

        return $table;
    }

    public function getWhere()
    {
        // Условия WHERE
        $wheres = array();

        foreach ($this->where as $where) {
            if (empty($where)) {
                continue;
            }

            $wheres[] = '(' . $where . ')';
        }

        $sql = join(' AND ', $wheres);

        if ($sql != '') {
            $sql = ' WHERE ' . $sql;
        }

        return $sql;
    }

    public function getJoins()
    {
        // JOIN'ы
        $sql = '';
        foreach ($this->joins as $type => $joins) {
            $type = strtoupper($type);

            if ($type != 'LEFT' && $type != 'INNER') {
                return '';
            }

            foreach ($joins as $table => $fields) {
                if ($table == '') {
                    return '';
                }

                $sql .= " $type JOIN $table ON ";

                $ons = array();

                foreach ($fields as $field => $val) {
                    if ($field == '') {
                        return '';
                    }

                    $op = '=';

                    if (is_array($val) &&
                        !empty($val[0]) &&
                        !empty($val[1]) &&
                        in_array($val[0], array('!=', '=', '>', '<', '<>'))
                    ) {
                        $op = $val[0];
                        $val = $val[1];
                    }

                    $on = $this->extractTableLink($table) . ".$field $op ";

                    if (is_array($val)) {
                        $keys = array_keys($val);
                        $vals = array_values($val);
                        if (empty($keys[0]) || empty($vals[0])) {
                            return '';
                        }

                        $on .= $keys[0] . '.' . $vals[0];
                    } else {
                        $on .= "'" . $val . "'";
                    }

                    $ons[] = $on;
                }

                $sql .= join(' AND ', $ons);
            }
        }
        return $sql;
    }

    public function getGroupBy()
    {
        if (empty($this->groupBy)) {
            return '';
        }
        return ' GROUP BY ' . join(', ', $this->groupBy);
    }

    public function getHaving()
    {
        if (empty($this->having)) {
            return '';
        }

        return ' HAVING ' . join(', ', $this->having);
    }

    public function getOrderBy()
    {
        $sql = join(', ', $this->orderBy);
        if (!empty($sql)) {
            $sql = ' ORDER BY ' . $sql;
        }

        return $sql;
    }

    public function getLimit()
    {
        $sql = '';
        if ($this->limitCount > 0 && $this->limitStart !== null) {
            $sql .= ' LIMIT ' . $this->limitStart . ', ' . $this->limitCount;
        } elseif ($this->limitCount > 0) {
            $sql .= ' LIMIT ' . $this->limitCount;
        }
        return $sql;
    }

    public function getUnions()
    {
        if (empty($this->unions)) {
            return '';
        }

        $sql = '';
        foreach ($this->unions as $union) {
            $sql .= ' UNION ';
            $sql .= $union->make();
        }

        return $sql;
    }

    public function getFields()
    {
        $sql = join(', ', $this->select);

        if ($sql == '') {
            $sql = '*';
        }

        return $sql;
    }

    public function getCountFields($what)
    {
        $flds = array();

        $keys = array_keys($this->groupBy);
        $vals = array_values($this->groupBy);

        if (!empty($keys[0]) && !empty($vals[0])) {
            $table = $keys[0];
            $field = $vals[0];
            $flds[] = 'COUNT(DISTINCT ' . $table . '.' . $field . ')';
        } elseif (is_string($what)) {
            $flds[] = 'COUNT(' . $what . ')';
        } else {
            $flds[] = 'COUNT(*)';
        }

        return join($flds);
    }

    public function make()
    {
        $sql = 'SELECT ';
        $sql .= $this->getFields();

        $sql .= ' FROM ' . $this->from;

        $sql .= $this->getJoins();
        $sql .= $this->getWhere();
        $sql .= $this->getGroupBy();

        $sql .= $this->getHaving();
        $sql .= $this->getOrderBy();
        $sql .= $this->getLimit();

        $sql .= $this->getUnions();

        return $sql;
    }

    public function debug()
    {
        echo $this->make();
        return $this;
    }

    public function get($a = false)
    {
        if (!empty($a)) {
            echo '<h1>deprecated usage! please replace ->get(id) to ->find($id)</h1>';
            echo '<pre>';
            debug_print_backtrace();
            echo '</pre>';
            exit;
        }

        if ($this->from == '') {
            return '';
        }

        return $this->db->table($this->make());
    }

    public function first()
    {
        $this->limit(1);

        return $this->db->row($this->make());
    }

    public function value($name, $useLimit = true)
    {
        if ($useLimit) {
            $this->limit(1);
        }

        return $this->db->one($this->select($name)->make());
    }

    public function column($name)
    {
        return $this->db->col($this->select($name)->make());
    }

    public function __toString()
    {
        return $this->make();
    }

    public function count($what = false)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->getCountFields($what);

        $sql .= ' FROM ' . $this->from;

        $sql .= $this->getJoins();
        $sql .= $this->getWhere();
        $sql .= $this->getGroupBy();

        return $this->db->one($sql);
    }

    private function aggregate($func, $what)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ' . $func . '(' . $what . ')';
        $sql .= ' FROM ' . $this->from;

        $sql .= $this->getJoins();
        $sql .= $this->getWhere();
        $sql .= $this->getGroupBy();

        return $this->db->one($sql);
    }

    public function max($what)
    {
        return $this->aggregate('max', $what);
    }

    public function min($what)
    {
        return $this->aggregate('min', $what);
    }

    public function avg($what)
    {
        return $this->aggregate('avg', $what);
    }

    public function sum($what)
    {
        return $this->aggregate('sum', $what);
    }

    public function exists()
    {
        return $this->limit(1)->count();
    }

    public function getSetCondition($data, $fields = false)
    {
        $first = true;
        $result = '';
        foreach ($data as $key => $value) {
            if (is_array($fields) && !in_array($key, $fields)) {
                continue;
            }

            if ($first) {
                $first = false;
            } else {
                $result .= ", ";
            }

            $result .= "`" . $key . "` = '" . $this->db->escape($value) . "'";
        }
        if ($first) {
            return false;
        }

        return $result;
    }

    public function getByCondition($field, $needle, $reverseCond = false)
    {
        $field = $this->db->escape($field);

        $cond = $this->from . "." . $field;

        if (is_array($needle)) {
            if ($reverseCond) {
                $cond .= " NOT IN (";
            } else {
                $cond .= " IN (";
            }
            $first = true;
            if ($needle) {
                foreach ($needle as $n) {
                    if (!$first) {
                        $cond .= ", ";
                    }
                    $cond .= "'" . $this->db->escape($n) . "'";
                    $first = false;
                }
            } else {
                $cond .= "''";
            }
            $cond .= ")";
        } else {
            if ($reverseCond) {
                $cond .= " <> '" . $this->db->escape($needle) . "'";
            } else {
                $cond .= " = '" . $this->db->escape($needle) . "'";
            }
        }

        return $cond;
    }

    public function insert($data, $fields = false)
    {
        if (empty($data) || !is_array($data) || count($data) == 0) return false;

        if (!is_array(reset($data))) {
            $q = "INSERT INTO `" . $this->from . "` SET " . $this->getSetCondition($data, $fields);
            return $this->db->query($q);
        }

        $keys = array_keys(current($data));

        if (is_array($fields)) {
            $keys = array_intersect($keys, $fields);
        }

        $sql = "";
        foreach ($data as $line) {
            if (!empty($sql)) {
                $sql .= ",";
            }

            $sql_line = "";
            foreach ($keys as $key) {
                if (!empty($sql_line)) {
                    $sql_line .= ",";
                }
                if (isset($line[$key])) {
                    $sql_line .= "'" . $line[$key] . "'";
                } else {
                    $sql_line .= "''";
                }
            }
            $sql .= "(" . $sql_line . ")";
        }

        if (empty($sql)) return false;

        $sql = "INSERT INTO " . $this->from . "(`" . join("`,`", $keys) . "`) VALUES" . $sql;
        return $this->db->query($sql);
    }

    public function insertGetId($data, $fields = false)
    {
        $this->insert($data, $fields);
        return $this->db->insertId();
    }

    public function put($data, $fields = false)
    {
        $set = $this->getSetCondition($data, $fields);
        if (empty($set)) return false;

        $sql = "
			INSERT INTO `" . $this->from . "` SET " . $set . "
			ON DUPLICATE KEY UPDATE " . $set . "
		";
        return $this->db->query($sql);
    }

    public function putGetId($data, $fields = false)
    {
        $this->put($data, $fields);
        return $this->db->insertId();
    }

    public function update($data, $fields = false)
    {
        if (empty($data) || !is_array($data) || count($data) == 0) return false;

        $set = $this->getSetCondition($data, $fields);
        if (empty($set)) return false;


        $sql = "UPDATE `" . $this->from . "` ";
        $sql .= $this->getJoins();
        $sql .= ' SET ' . $set;
        $sql .= $this->getWhere();
        return $this->db->query($sql);
    }

    public function delete()
    {
        $sql = "DELETE " . $this->table . " FROM " . $this->from . ' ';
        $sql .= $this->getJoins();
        $sql .= $this->getWhere();
        return $this->db->query($sql);
    }

    public function truncate()
    {
        $sql = "TRUNCATE $this->from";
        return $this->db->query($sql);
    }

    public function copyGetId($replaces = array())
    {
        $this->copy($replaces);
        return $this->db->insertId();
    }

    public function copy($replaces = array())
    {
        $fields = array_diff(array_keys($this->fields), array($this->primaryKey));

        $select = $fields;
        foreach ($select as $k => $selectField) {
            if (isset($replaces[$selectField])) {
                $select[$k] = "'$replaces[$selectField]'";
            }
        }

        $sql = "
            INSERT INTO $this->table (" . implode(",", $fields) . ")
            SELECT " . implode(",", $select) . "
        ";

        $sql .= ' FROM ' . $this->from;
        $sql .= $this->getWhere();

        return $this->db->query($sql);
    }

}
