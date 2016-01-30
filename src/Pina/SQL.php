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
    
    protected function selected()
    {
        return count($this->select) > 0;
    }

    public function join($type, $table, $field, $table2 = false, $field2 = false)
    {
        if (!empty($table2) && !empty($field2)) {
            $this->joins[] = array($type, $table, array($field => array($table2 => $field2)));
        } else if (is_array($field)) {
            $this->joins[] = array($type, $table, $field);
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
        return $this->where($this->getByCondition($field, $needle, '='));
    }

    public function whereNotBy($field, $needle)
    {
        return $this->where($this->getByCondition($field, $needle, '<>'));
    }
    
    public function whereLike($field, $needle)
    {
        return $this->where($this->getByCondition($field, $needle, 'LIKE'));
    }
    
    public function whereNotLike($field, $needle)
    {
        return $this->where($this->getByCondition($field, $needle, 'NOT LIKE'));
    }
    
    public function whereBetween($field, $start, $end)
    {
        return $this->where($this->db->escape($field).' BETWEEN '.$this->db->escape($start).' AND '.$this->db->escape($end));
    }
    
    public function whereNull($field)
    {
        return $this->where($field . ' IS NULL');
    }
    
    public function whereNotNull($field)
    {
        return $this->where($field . ' IS NOT NULL');
    }
    
    public function whereFields($ps)
    {
        if (!is_array($ps)) {
            return;
        }
        
        foreach ($ps as $k => $v) {
            $this->whereBy($k, $v);
        }
        return $this;
    }

    public function groupBy($table, $field = false)
    {
        if (empty($field)) {
            $this->groupBy[] = $table;
            return $this;
        }
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

    public function paging(&$paging, $field = false)
    {
        $paging->setTotal($this->pagingCount($field));
        
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
        foreach ($this->joins as $line) {
            list($type, $table, $fields) = $line;
            $type = strtoupper($type);

            if ($type != 'LEFT' && $type != 'INNER') {
                return '';
            }

            if ($table == '') {
                return '';
            }

            $tableSql = " $type JOIN $table ON ";

            $ons = array();

            foreach ($fields as $field => $val) {
                if ($field == '') {
                    Log::error('SQL', 'empty join field '.print_r($line, 1));
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
                        Log::error('SQL', 'bad join field '.print_r($line, 1));
                        continue;
                    }

                    $on .= $keys[0] . '.' . $vals[0];
                } else {
                    $on .= "'" . $val . "'";
                }

                $ons[] = $on;
            }

            $sql .= $tableSql.join(' AND ', $ons);
            
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

    public function getCountFields($field)
    {        
        if (empty($field)) {
            $field = '*';
        }

        if (is_string($field)) {
            $flds[] = 'COUNT(' . $field . ')';
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
        $oldSelect = $this->select;
        $this->select = array();
        $sql = $this->select($name)->make();
        $this->select = $oldSelect;
        return $this->db->col($sql);
    }

    public function __toString()
    {
        return $this->make();
    }
    
    public function pagingCount($field = false)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->getCountFields($field);

        $sql .= ' FROM ' . $this->from;

        $sql .= $this->getJoins();
        $sql .= $this->getWhere();

        return $this->db->one($sql);
    }

    public function count($field = false)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->getCountFields($field);

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
    
    public function getByCondition($fields, $needle, $operand = '=')
    {
        if (!is_array($fields)) {
            return $this->getSimpleByCondition($fields, $needle, $operand);
        }
        
        $fields = array_filter($fields);
        
        $q = '';
        foreach ($fields as $field) {
            if (!empty($q)) {
                $q .= ' OR ';
            }
            $q .= $this->getSimpleByCondition($field, $needle, $operand);
        }
        return '('.$q.')';
    }
    
    private function getSimpleByCondition($field, $needle, $operand = '=')
    {
        $field = $this->db->escape($field);

        $fieldCondition = strpos($field, '.')===false?($this->from . "." . $field):$field;

        if (is_array($needle)) {
            switch ($operand) {
                case '<>': return $fieldCondition . " NOT IN " . $this->getInCondition($needle);
                case '=': return $fieldCondition . " IN " . $this->getInCondition($needle);
            }
            
            $condition = '';
            foreach ($needle as $n) {
                if (!empty($condition)) {
                    $condition .= ' OR ';
                }
                $condition .= $this->getByCondition($field, $n, $operand);
            }
            
            return $condition;

        } elseif (in_array($operand, array('<>', '=', 'LIKE', 'NOT LIKE'))) {
            return $fieldCondition . ' ' . $operand . " '" . $this->db->escape($needle) . "'";
        }
        
        return '';
    }
    
    public function getInCondition($needle)
    {
        $first = true;
        $condition = '(';
        if ($needle) {
            foreach ($needle as $n) {
                if (!$first) {
                    $condition .= ",";
                }
                $condition .= "'" . $this->db->escape($n) . "'";
                $first = false;
            }
        } else {
            $condition .= "''";
        }
        $condition .= ")";
        return $condition;

    }


    public function insert($data, $fields = false)
    {
        if (empty($data) || !is_array($data) || count($data) == 0) {
            return false;
        }

        if (!is_array(reset($data))) {
            $q = "INSERT INTO `" . $this->from . "` SET " . $this->getSetCondition($data, $fields);
            return $this->db->query($q);
        }

        list($keys, $values) = $this->getKeyValuesCondition($data, $fields);

        $sql = "INSERT INTO " . $this->from . "(`" . join("`,`", $keys) . "`) VALUES" . $values;
        return $this->db->query($sql);
    }

    public function insertGetId($data, $fields = false)
    {
        $this->insert($data, $fields);
        return $this->db->insertId();
    }

    public function put($data, $fields = false)
    {
        if (empty($data) || !is_array($data) || count($data) == 0) {
            return false;
        }
        
        if (!is_array(reset($data))) {
            $set = $this->getSetCondition($data, $fields);
            if (empty($set)) {
                return false;
            }

            $sql = "
                INSERT INTO `" . $this->from . "` SET " . $set . "
                ON DUPLICATE KEY UPDATE " . $set . "
            ";
            return $this->db->query($sql);
        }
        
        list($keys, $values) = $this->getKeyValuesCondition($data, $fields);
        $onDuplicate = $this->getOnDuplicateKeyCondition($keys);

        $sql = "INSERT INTO " . $this->from . "(`" . join("`,`", $keys) . "`) VALUES " . $values;
        if (!empty($onDuplicate)) {
            $sql .= " ON DUPLICATE KEY UPDATE ".$onDuplicate;
        }
        return $this->db->query($sql);
    }

    public function putGetId($data, $fields = false)
    {
        $this->put($data, $fields);
        return $this->db->insertId();
    }
    
    private function getOnDuplicateKeyCondition($keys)
    {
        $keys = $this->getOnDuplicateKeys($keys);
        if (empty($keys) || !is_array($keys)) {
            return '';
        }
        
        $q = '';
        foreach ($keys as $key) {
            if (!empty($q)) {
                $q .= ',';
            }
            $q .= $key.' = VALUES('.$key.')';
        }
        return $q;
    }
    
    protected function getOnDuplicateKeys($keys)
    {
        return $keys;
    }
    
    private function getKeyValuesCondition($data, $fields)
    {
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

        if (empty($sql)) {
            return false;
        }
        
        return array($keys, $sql);
    }

    public function update($data, $fields = false)
    {
        if (empty($data) || !is_array($data) || count($data) == 0) {
            return false;
        }

        $set = $this->getSetCondition($data, $fields);
        if (empty($set)) {
            return false;
        }


        $sql = "UPDATE `" . $this->from . "` ";
        $sql .= $this->getJoins();
        $sql .= ' SET ' . $set;
        $sql .= $this->getWhere();
        $this->db->query($sql);
        return $this->db->affectedRows();
    }
    
    public function increment($field, $value)
    {
        return $this->updateOperation('`' . $field . '` = `' . $field . '` + ' . $this->db->escape($value));
    }
    
    public function decrement($field, $value)
    {
        return $this->updateOperation('`' . $field . '` = `' . $field . '` - ' . $this->db->escape($value));
    }
    
    protected function updateOperation($operation)
    {
        $sql = "UPDATE " . $this->from . " ";
        $sql .= $this->getJoins();
        $sql .= ' SET ' . $operation;
        $sql .= $this->getWhere();
        $this->db->query($sql);
        return $this->db->affectedRows();
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
    
    public function startTransaction()
    {
        return $this->db->query("START TRANSACTION");
    }

    public function commit()
    {
        return $this->db->query("COMMIT");
    }

    public function rollback()
    {
        return $this->db->query("ROLLBACK");
    }    
}
