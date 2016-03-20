<?php

namespace Pina;

/*
 * TODO:
 * 1) добавить возможность использовать подзапросы в select/from/where
 * 2) для Join возможность использовать and/or в условии соединения
 * 3) автоматически вычислять, какой join нужен для подсчета постраничной 
 * навигации, а какой нет
 */

class SQL
{
    
    const SQL_OPERAND_FIELD = 0;
    const SQL_OPERAND_VALUE = 1;

    public $db = '';
    private $select = array();
    private $from = '';
    private $alias = '';
    private $joins = array();
    private $where = array();
    private $groupBy = array();
    private $having = array();
    private $orderBy = array();
    private $limitStart = 0;
    private $limitCount = 0;
    private $unions = array();
    private $ons = array();

    public static function table($table, $db = false)
    {
        return new SQL($table, $db);
    }
    
    public static function subquery($query)
    {
        return new SQL('('.$query->make().')', DB::get());
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
        $this->ons = array();
        return $this;
    }

    protected function __construct($table, $db = false)
    {
        $this->db = $db ? $db : DB::get();
        $this->from = $table;
    }
    
    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }
    
    public function getAlias()
    {
        return $this->alias?$this->alias:$this->from;
    }
    
    public function makeFrom()
    {
        return $this->from.($this->alias?(' '.$this->alias):'');
    }

    public function select($field)
    {
        $fields = explode(',', $field);
        foreach ($fields as $k => $v) {
            $fields[$k] = trim($v);
        }
        
        $this->select = array_merge($this->select, $fields);
        return $this;
    }
    
    protected function selected()
    {
        return count($this->select) > 0;
    }

    public function join($type, $table, $field = false, $table2 = false, $field2 = false)
    {
        if (empty($field)) {
            //join SQL builder class
            $this->joins[] = array($type, $table);
        }
        if (!empty($table2) && !empty($field2)) {
            $this->joins[] = array($type, $table, array($field => array($table2 => $field2)));
        } else if (is_array($field)) {
            $this->joins[] = array($type, $table, $field);
        }
        return $this;
    }

    public function leftJoin($table, $field = false, $table2 = false, $field2 = false)
    {
        return $this->join('LEFT', $table, $field, $table2, $field2);
    }

    public function innerJoin($table, $field = false, $table2 = false, $field2 = false)
    {
        return $this->join('INNER', $table, $field, $table2, $field2);
    }

    public function rightJoin($table, $field = false, $table2 = false, $field2 = false)
    {
        return $this->join('RIGHT', $table, $field, $table2, $field2);
    }
    
    public function on($field1, $field2 = '')
    {
        $this->ons[] = array('=', self::SQL_OPERAND_FIELD, $field1, self::SQL_OPERAND_FIELD, $field2?$field2:$field1);
        return $this;
    }
    
    public function onBy($field, $needle, $op = '=')
    {
        $this->ons[] = array($op, self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle);
        return $this;
    }
    
    public function onNotBy($field, $needle)
    {
        $this->ons[] = array('<>', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle);
        return $this;
    }
    
    public function makeOns($parentAlias)
    {
        $q = '';
        foreach ($this->ons as $on) {
            if (!empty($q)) {
                $q .= ' AND ';
            }
            $q .= $this->makeByCondition($on, $parentAlias);
        }
        return ' ON '.$q;
    }

    public function where($condition)
    {
        $this->where[] = $condition;
        return $this;
    }

    public function whereBy($field, $needle)
    {
        return $this->where($this->makeByCondition(array('=', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    public function whereNotBy($field, $needle)
    {
        return $this->where($this->makeByCondition(array('<>', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }
    
    public function whereLike($field, $needle)
    {
        return $this->where($this->makeByCondition(array('LIKE', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }
    
    public function whereNotLike($field, $needle)
    {
        return $this->where($this->makeByCondition(array('NOT LIKE', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }
    
    public function whereBetween($field, $start, $end)
    {
        return $this->where($this->makeByCondition(array('BETWEEN', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $start, self::SQL_OPERAND_VALUE, $end)));
    }
    
    public function whereNotBetween($field, $start, $end)
    {
        return $this->where($this->makeByCondition(array('NOT BETWEEN', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $start, self::SQL_OPERAND_VALUE, $end)));
    }
    
    public function whereNull($field)
    {
        return $this->where($this->makeByCondition(array('IS NULL', self::SQL_OPERAND_FIELD, $field)));
    }
    
    public function whereNotNull($field)
    {
        return $this->where($this->makeByCondition(array('IS NOT NULL', self::SQL_OPERAND_FIELD, $field)));
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

    public function paging(&$paging, $field = false, $useJoin = true)
    {
        $paging->setTotal($this->pagingCount($field, $useJoin));
        
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

    public function makeWhere()
    {
        $sql = join(' AND ', $this->getWhereArray());
        
        if ($sql != '') {
            $sql = ' WHERE ' . $sql;
        }

        return $sql;
    }
    
    public function getWhereArray()
    {
        $wheres = array();
        foreach ($this->where as $where) {
            if (empty($where)) {
                continue;
            }

            $wheres[] = '(' . $where . ')';
        }
        
        return array_merge($wheres, $this->getJoinWhereArray());
    }
    
    public function getJoinWhereArray()
    {
        $wheres = array();
        foreach ($this->joins as $line) {
            if (count($line) == 2) {
                list($type, $table) = $line;
                $wheres = array_merge($wheres, $table->getWhereArray());
            }
        }
        return $wheres;
    }

    public function makeJoins()
    {
        $sql = '';
        foreach ($this->joins as $line) {
            
            if (count($line) == 2) {
                list($type, $table) = $line;

                $joinSql = ' '.$type.' JOIN ';
                $joinSql .= $table->makeFrom();
                $joinSql .= $table->makeOns($this->getAlias());
                
                $joinSql .= $table->makeJoins();
                
                $sql .= $joinSql;
                
                continue;
            }
            
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

                $on = $this->extractTableLink($table) . ".$field ";

                if (is_array($val)) {
                    $keys = array_keys($val);
                    $vals = array_values($val);
                    if (empty($keys[0]) || empty($vals[0])) {
                        $op = ($op === '=') ? 'IN' : 'NOT IN';
                        $on .= $op . " ('" . join("','", $val) . "')";
                    } else {
                        $on .= $op . ' ' . $keys[0] . '.' . $vals[0];
                    }
                } else {
                    $on .= $op . "'" . $val . "'";
                }

                $ons[] = $on;
            }

            $sql .= $tableSql.join(' AND ', $ons);
            
        }
        return $sql;
    }

    public function makeGroupBy()
    {
        if (empty($this->groupBy)) {
            return '';
        }
        return ' GROUP BY ' . join(', ', $this->groupBy);
    }

    public function makeHaving()
    {
        if (empty($this->having)) {
            return '';
        }

        return ' HAVING ' . join(', ', $this->having);
    }

    public function makeOrderBy()
    {
        $sql = join(', ', $this->orderBy);
        if (!empty($sql)) {
            $sql = ' ORDER BY ' . $sql;
        }

        return $sql;
    }

    public function makeLimit()
    {
        $sql = '';
        if ($this->limitCount > 0 && $this->limitStart !== null) {
            $sql .= ' LIMIT ' . $this->limitStart . ', ' . $this->limitCount;
        } elseif ($this->limitCount > 0) {
            $sql .= ' LIMIT ' . $this->limitCount;
        }
        return $sql;
    }

    public function makeUnions()
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

    public function makeFields()
    {
        $fields = $this->getFieldArray();
        $sql = join(', ', $fields);

        if ($sql == '') {
            $sql = '*';
        }

        return $sql;
    }
    
    public function getFieldArray()
    {
        $fields = array();
        foreach ($this->select as $k => $v) {
            if (strpos($v, '.') === false) {
                $fields[] = $this->getAlias().'.'.$v;
            } else {
                $fields[] = $v;
            }
        }
        $fields = array_merge($fields, $this->getJoinFieldArray());
        return $fields;
    }
    
    public function getJoinFieldArray()
    {
        $fields = array();
        foreach ($this->joins as $line) {
            if (count($line) == 2) {
                list($type, $table) = $line;
                $fields = array_merge($fields, $table->getFieldArray());
            }
        }
        return $fields;
    }

    public function makeCountFields($field)
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
        $sql .= $this->makeFields();

        $sql .= ' FROM ' . $this->makeFrom();

        $sql .= $this->makeJoins();
        $sql .= $this->makeWhere();
        $sql .= $this->makeGroupBy();

        $sql .= $this->makeHaving();
        $sql .= $this->makeOrderBy();
        $sql .= $this->makeLimit();

        $sql .= $this->makeUnions();

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
    
    public function pagingCount($field = false, $useJoin = true)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->makeCountFields($field);

        $sql .= ' FROM ' . $this->from;

        if (!empty($useJoin)) {
            $sql .= $this->makeJoins();
        }
        $sql .= $this->makeWhere();

        return $this->db->one($sql);
    }

    public function count($field = false)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->makeCountFields($field);

        $sql .= ' FROM ' . $this->from;

        $sql .= $this->makeJoins();
        $sql .= $this->makeWhere();
        
        $sql .= $this->makeGroupBy();

        return $this->db->one($sql);
    }

    private function aggregate($func, $what)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ' . $func . '(' . $what . ')';
        $sql .= ' FROM ' . $this->from;

        $sql .= $this->makeJoins();
        $sql .= $this->makeWhere();
        $sql .= $this->makeGroupBy();

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

    public function makeSetCondition($data, $fields = false)
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
    
    public function makeByCondition($condition, $parentAlias = '')//$fields, $needle, $operand = '='
    {
        
        $operation = $condition[0];
        
        for ($i = 1; $i < count($condition); $i += 2) {
            $type = $condition[$i];
            $operand = $condition[$i + 1];
            $isOrCondition = is_array($operand) 
                && ($type === self::SQL_OPERAND_FIELD || !in_array($operation, array('=', '<>', 'IN', 'NOT IN')));
            if ($isOrCondition) {
                $q = '';
                foreach ($operand as $item) {
                    if (empty($item)) {
                        continue;
                    }
                    if (!empty($q)) {
                        $q .= ' OR ';
                    }                    
                    $simpleCondition = $condition;
                    $simpleCondition[$i + 1] = $item;
                    $q .= $this->makeByCondition($simpleCondition, $parentAlias);
                }
                return $q;
            }
        }
        switch ($operation) {
            case '=':
            case '<>':
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'IN':
            case 'NOT IN':
            case 'LIKE':
            case 'NOT LIKE':    
                return $this->getBinaryCondition($condition, $parentAlias);
            case 'IS NULL':
            case 'IS NOT NULL':
                return $this->getUnaryPostfixCondition($condition, $parentAlias);
            case 'NOT':
                return $this->getUnaryPrefixCondition($condition, $parentAlias);
            case 'BETWEEN':
            case 'NOT BETWEEN':
                return $this->getBetweenCondition($condition, $parentAlias);
        }
        return '';
    }
    
    private function getBinaryCondition($condition, $parentAlias = '')
    {
        list($operation, $type1, $operand1, $type2, $operand2) = $condition;
        
        return $this->getOperand('', $type1, $operand1).' '.$this->getOperand($operation, $type2, $operand2, $parentAlias);
    }
    
    private function getBetweenCondition($condition, $parentAlias)
    {
        list($operation, $type1, $operand1, $type2, $operand2, $type3, $operand3) = $condition;
                
        return $this->getOperand('', $type1, $operand1).' '.$this->getOperand($operation, $type2, $operand2, $parentAlias).' AND '.$this->getOperand('', $type3, $operand3, $parentAlias);
    }
    
    private function getOperand($operation, $type, $operand, $alias = '')
    {
        if (is_array($operand) && $type === self::SQL_OPERAND_VALUE && empty($operation)) {
            throw new \Exception('unsupported format');
        }
        
        $prefix = $operation?$operation.' ':'';
        if ($type === self::SQL_OPERAND_FIELD) {
            if (strpos($operand, '.')) {
                return $prefix.$operand;
            }
            return $prefix.($alias?$alias:$this->getAlias()).'.'.$operand;
        }
        
        if (is_array($operand)) {
            if ($operation === '=') {
                return 'IN '.$this->getInCondition($operand);
            } else if ($operation === '<>') {
                return 'NOT IN '.$this->getInCondition($operand);
            } else if ($operation === 'IN' || $operation === 'NOT IN') {
                return $prefix.$this->getInCondition($needle);
            }
            
            throw new \Exception('bad array operation');
            return '';
        }
        
        return $prefix."'".$this->db->escape($operand)."'";
        
    }
    /*
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
                $condition .= $this->makeByCondition($field, $n, $operand);
            }
            
            return $condition;

        } elseif (in_array($operand, array('<>', '=', 'LIKE', 'NOT LIKE'))) {
            return $fieldCondition . ' ' . $operand . " '" . $this->db->escape($needle) . "'";
        }
        
        return '';
    }
    */
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
            $q = "INSERT INTO `" . $this->from . "` SET " . $this->makeSetCondition($data, $fields);
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
            $set = $this->makeSetCondition($data, $fields);
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

        $set = $this->makeSetCondition($data, $fields);
        if (empty($set)) {
            return false;
        }


        $sql = "UPDATE `" . $this->from . "` ";
        $sql .= $this->makeJoins();
        $sql .= ' SET ' . $set;
        $sql .= $this->makeWhere();
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
        $sql .= $this->makeJoins();
        $sql .= ' SET ' . $operation;
        $sql .= $this->makeWhere();
        $this->db->query($sql);
        return $this->db->affectedRows();
    }

    public function delete()
    {
        $sql = "DELETE " . $this->table . " FROM " . $this->from . ' ';
        $sql .= $this->makeJoins();
        $sql .= $this->makeWhere();
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
        $sql .= $this->makeWhere();

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
