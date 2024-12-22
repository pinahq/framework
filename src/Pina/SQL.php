<?php

namespace Pina;

/*
 * TODO:
 * 1) добавить возможность использовать подзапросы в select/from/where
 * 2) для Join возможность использовать and/or в условии соединения
 * 3) автоматически вычислять, какой join нужен для подсчета постраничной
 * навигации, а какой нет
 */

use Exception;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Field;
use Pina\Data\FieldSet;
use Pina\Data\Schema;
use Pina\Types\StringType;

class SQL
{

    const SQL_OPERAND_FIELD = 0;
    const SQL_OPERAND_VALUE = 1;
    const SQL_SELECT_FIELD = 0;
    const SQL_SELECT_CONDITION = 1;

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
    private $limitCount = -1;
    private $unions = array();
    private $ons = array();

    protected $context = array();

    public function __clone()
    {
        foreach ($this->joins as $k => $join) {
            $this->joins[$k][1] = clone($join[1]);
        }
    }

    /**
     * Создает объект запроса к БД на основе имени таблицы и драйвера БД
     * @param string $table
     * @param DatabaseDriverInterface $db
     * @return \Pina\SQL
     */
    public static function table($table, $db = false)
    {
        return new SQL($table, $db);
    }

    /**
     * Создает объект подзапроса на основании текста запроса или объекта другого запроса
     * @param mixed $query
     * @return \Pina\SQL
     */
    public static function subquery($query)
    {
        return new SQL($query);
    }

    /**
     * Обнуляет запрос
     * @return $this
     */
    public function init()
    {
        $this->select = array();
        $this->joins = array();
        $this->where = array();
        $this->groupBy = array();
        $this->having = array();
        $this->orderBy = array();
        $this->limitStart = 0;
        $this->limitCount = -1;
        $this->unions = array();
        $this->ons = array();
        return $this;
    }

    /**
     * Создает и возвращает копию запроса
     * @return $this
     */
    public function cloneObject()
    {
        return clone $this;
    }

    /**
     * Создает конструктор запроса
     * @param string $table
     * @param DatabaseDriverInterface $db
     */
    protected function __construct($table, $db = null)
    {
        $this->db = $db ? $db : App::container()->get(DatabaseDriverInterface::class);
        $this->from = $table;
    }

    /**
     * Добавляет контекст выполнения запроса.
     * Контекст используется как в выборке, так и при вставке
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function context($field, $value)
    {
        $this->context[$field] = $value;
        return $this;
    }

    /**
     * Возвращает схему таблицы
     * @return Schema
     */
    public function getSchema()
    {
        return new Schema();
    }

    /**
     * Возвращает схему на основании выбранных полей задействованных в запросе таблиц
     * @return Schema
     * @throws NotFoundException
     */
    public function getQuerySchema()
    {
        $schema = $this->grabQuerySchema();
        if ($schema->isEmpty()) {
            return $this->getSchema();
        }
        return $schema;
    }

    public function getDataTable(): DataTable
    {
        return new DataTable($this->get(), $this->getQuerySchema());
    }

    public function findDataRecord($id): DataRecord
    {
        return new DataRecord($this->find($id), $this->getQuerySchema());
    }

    public function findDataRecordOrFail($id): DataRecord
    {
        return new DataRecord($this->findOrFail($id), $this->getQuerySchema());
    }

    public function firstDataRecord(): DataRecord
    {
        return new DataRecord($this->first(), $this->getQuerySchema());
    }

    public function firstDataRecordOrFail(): DataRecord
    {
        return new DataRecord($this->firstOrFail(), $this->getQuerySchema());
    }

    /**
     * @return Schema
     * @throws NotFoundException
     */
    protected function grabQuerySchema()
    {
        $tableSchema = $this->getSchema();

        $schema = new Schema();
        $selected = new FieldSet($tableSchema);
        foreach ($this->select as $s) {
            $selecType = $s[0];
            $field = $s[1];
            $alias = $s[2] ?? $field;
            $title = $s[3] ?? $field;
            $fieldType = $s[4] ?? StringType::class;

            if ($field == '*') {
                $schema->merge($tableSchema);
                continue;
            }

            if ($tableSchema->has($field)) {
                $selected->select($field);
            } else {
                $f = new Field($field, $title, $fieldType);
                $f->setAlias($alias);
                if ($selecType == self::SQL_SELECT_CONDITION) {
                    $f->setStatic();
                }
                $selected->add($f);
                continue;
            }
            if ($title && $title <> $field) {
                $selected->setTitle($field, $title);
            }

            if ($alias) {
                $selected->setAlias($field, $alias);
            }
        }
        $schema->merge($selected->makeSchema());

        foreach ($this->joins as $line) {
            list($type, $table) = $line;

            /** @var SQL $table */
            $schema->merge($table->grabQuerySchema());
        }

        return $schema;
    }


    /**
     * @param string $needleField
     * @return $this|null
     */
    public function resolveFieldTable(Field $field)
    {
        foreach ($this->select as $s) {
            $alias = $s[2] ?? $s[1];

            if ($field->match($alias)) {
                return $this;
            }

            if ($s[1] == '*') {
                $keys = $this->getSchema()->getFieldKeys();
                foreach ($keys as $key) {
                    if ($field->match($key)) {
                        return $this;
                    }
                }
            }
        }


        foreach ($this->joins as $line) {
            list($type, $table) = $line;

            /** @var SQL $table */
            $r = $table->resolveFieldTable($field);
            if (!is_null($r)) {
                return $r;
            }
        }

        return null;

    }

    /**
     * Добавляет в запрос alias для таблицы
     * @param string $alias
     * @return $this
     */
    public function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Возвращает alias таблицы или имя таблицы, если alias не настроен
     * @return string
     */
    public function getAlias()
    {
        return $this->alias ? ('`' . $this->alias . '`') : $this->getFrom();
    }

    /**
     * Возвращает имя таблицы или вложенный запрос вместо него
     * @return string
     */
    public function getFrom()
    {
        if (is_string($this->from)) {
            return '`' . $this->from . '`';
        }

        return '(' . $this->from . ')';
    }

    /**
     * Собирает конструкцию для FROM
     * @return string
     */
    public function makeFrom()
    {
        return $this->getFrom() . ($this->alias ? (' `' . $this->alias . '`') : '');
    }

    /**
     * Сбрасывает список выбранных полей
     * @return $this
     */
    public function resetSelect()
    {
        $this->select = [];
        foreach ($this->joins as $k => $v) {
            $this->joins[$k][1]->resetSelect();
        }
        return $this;
    }

    /**
     * Добавляет в запрос выбор поля, если оно еще не выбрано
     * @param string $field
     * @return $this
     */
    public function selectIfNotSelected($field)
    {
        if ($this->isSelected($field)) {
            return $this;
        }

        return $this->select($field);
    }

    /**
     * Добавляет в запрос выбор поля, если алиас еще не занят
     * @param string $field
     * @return $this
     */
    public function selectAsIfNotSelected($field, $alias)
    {
        if ($this->isSelectedAs($field, $alias)) {
            return $this;
        }

        return $this->selectAs($field, $alias);
    }

    public function isSelected($field)
    {
        foreach ($this->select as $item) {
            if (isset($item[1]) && $item[1] == $field) {
                return true;
            }
            if (isset($item[2]) && $item[2] == $field) {
                return true;
            }
        }

        foreach ($this->joins as $k => $v) {
            if ($this->joins[$k][1]->isSelected($field)) {
                return true;
            }
        }

        return false;
    }

    public function isSelectedAs($alias)
    {
        foreach ($this->select as $item) {
            if (isset($item[2]) && $item[2] == $alias) {
                return true;
            }
        }

        foreach ($this->joins as $k => $v) {
            if ($this->joins[$k][1]->isSelectedAs($alias)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Добавляет в запрос выбор поля
     * @param string $field
     * @return $this
     */
    public function select($field)
    {
        $fields = is_array($field) ? $field : array($field);
        foreach ($fields as $v) {
            $this->select[] = array(self::SQL_SELECT_FIELD, trim($v));
        }
        return $this;
    }

    /**
     * Добавляет в запрос выбор поля с псевданимом
     * @param string $field
     * @param string $alias
     * @return $this
     */
    public function selectAs($field, $alias, $title = '')
    {
        $this->select[] = array(self::SQL_SELECT_FIELD, trim($field), trim($alias), $title);
        return $this;
    }

    /**
     * Добавляет в запрос выбор поля с префиксом
     * @param string $field
     * @param string $prefix
     * @return $this
     */
    public function selectWithPrefix($field, $prefix)
    {
        $this->select[] = array(self::SQL_SELECT_FIELD, trim($field), trim($prefix) . '_' . trim($field));
        return $this;
    }

    /**
     * Добавляет в запрос вычисление поля с псевданимом
     * @param string $field
     * @param string $alias
     * @return $this
     */
    public function calculate($field, $alias = null, $title = '', $type = null)
    {
        $this->select[] = array(self::SQL_SELECT_CONDITION, $field, $alias, $title, $type);
        return $this;
    }

    /**
     * Проверяет, есть ли в запросе конструкции на выбор поля
     * @return int
     */
    protected function selected()
    {
        return count($this->select) > 0;
    }

    /**
     * Добавляет в запрос JOIN другой таблицы
     * @param string $type
     * @param \Pina\SQL $table
     * @return $this
     */
    public function join($type, $table)
    {
        $this->joins[] = array($type, $table);
        return $this;
    }

    /**
     * Добавляет в запрос LEFT JOIN другой таблицы
     * @param \Pina\SQL $table
     * @return $this
     */
    public function leftJoin($table)
    {
        return $this->join('LEFT', $table);
    }

    /**
     * Добавляет в запрос INNER JOIN другой таблицы
     * @param \Pina\SQL $table
     * @return $this
     */
    public function innerJoin($table)
    {
        return $this->join('INNER', $table);
    }

    /**
     * Добавляет в запрос RIGHT JOIN другой таблицы
     * @param \Pina\SQL $table
     * @return $this
     */
    public function rightJoin($table)
    {
        return $this->join('RIGHT', $table);
    }

    /**
     * Добавляет в запрос CROSS JOIN другой таблицы
     * @param \Pina\SQL $table
     * @return $this
     */
    public function crossJoin($table)
    {
        return $this->join('CROSS', $table);
    }

    /**
     * Добавляет в запрос условие соединения, по которому текущая таблица присоединяется к вышестоящей
     * Условие задается как имена полей текущей и вышестоящей таблицы, значения которых должны быть равны
     * @param string $field1
     * @param string $field2
     * @return $this
     */
    public function on($field1, $field2 = '')
    {
        $this->ons[] = array('=', self::SQL_OPERAND_FIELD, $field1, self::SQL_OPERAND_FIELD, $field2 ? $field2 : $field1);
        return $this;
    }

    /**
     * Добавляет в запрос условие соединения, по которому текущая таблица присоединяется к вышестоящей
     * Условие задается как отношение поля к некоторому значению или массиву значений
     * @param string $field
     * @param array|string|int|float $needle
     * @param string $op
     * @return $this
     */
    public function onBy($field, $needle, $op = '=')
    {
        $this->ons[] = array($op, self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle);
        return $this;
    }

    /**
     * Добавляет в запрос условие соединения, по которому текущая таблица присоединяется к вышестоящей
     * Условие задается как отношение поля к некоторому значению или массиву значений
     * @param string $field
     * @param array|string|int|float $needle
     * @param string $op
     * @return $this
     */
    public function onNotBy($field, $needle)
    {
        $this->ons[] = array('<>', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle);
        return $this;
    }

    /**
     * Добавляет в запрос условие соединения, по которому текущая таблица присоединяется к вышестоящей
     * Условие может быть произвольным, но соответствовать формату
     * @param array|string $condition
     * @return $this
     */
    public function onRaw($condition)
    {
        $this->ons[] = $condition;
        return $this;
    }

    /**
     * Собирает и возвращает текст ON-части запроса
     * @param string $parentAlias
     * @return string
     */
    public function makeOns($parentAlias)
    {
        $q = '';
        foreach ($this->ons as $on) {
            if (!empty($q)) {
                $q .= ' AND ';
            }
            if (is_array($on)) {
                $q .= $this->makeByCondition($on, $parentAlias);
            } else {
                $q .= '(' . $on . ')';
            }
        }

        foreach ($this->context as $k => $v) {
            if (!empty($q)) {
                $q .= ' AND ';
            }
            $q .= $this->makeByCondition(array('=', self::SQL_OPERAND_FIELD, $k, self::SQL_OPERAND_VALUE, $v), $parentAlias);
        }

        return !empty($q) ? (' ON ' . $q) : ' ON 1 ';
    }

    /**
     * Добавляет в запрос произвольное условие выборки
     * @param string $condition
     * @return $this
     */
    public function where($condition)
    {
        $this->where[] = $condition;
        return $this;
    }

    /**
     * Добавляет в запрос условие выборки, основанное на равенстве поля заданному значению
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * Значение может быть как строкой, так и массивом. В случае массива сформируется конструкция IN
     * @param array|string $field
     * @param array|string|int|float $needle
     * @return $this
     */
    public function whereBy($field, $needle)
    {
        return $this->where($this->makeWhereBy($field, $needle));
    }

    protected function makeWhereBy($field, $needle)
    {
        return $this->makeByCondition(array('=', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle));
    }

    /**
     * Добавляет в запрос условие выборки "$field больше чем $needle"
     * @param array|string $field
     * @param array|string|int|float $needle
     * @return $this
     */
    public function whereGreaterThan($field, $needle)
    {
        return $this->where($this->makeByCondition(array('>', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки "$field больше или равно $needle"
     * @param array|string $field
     * @param array|string|int|float $needle
     * @return $this
     */
    public function whereGreaterOrEqual($field, $needle)
    {
        return $this->where($this->makeByCondition(array('>=', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки "$field меньше чем $needle"
     * @param array|string $field
     * @param array|string|int|float $needle
     * @return $this
     */
    public function whereLessThan($field, $needle)
    {
        return $this->where($this->makeByCondition(array('<', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки "$field меньше или равно $needle"
     * @param array|string $field
     * @param array|string|int|float $needle
     * @return $this
     */
    public function whereLessOrEqual($field, $needle)
    {
        return $this->where($this->makeByCondition(array('<=', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на неравенстве поля заданному значению
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * Значение может быть как строкой, так и массивом. В случае массива сформируется конструкция NOT IN
     * @param array|string $field
     * @param array|string|int|float $needle
     * @return $this
     */
    public function whereNotBy($field, $needle)
    {
        return $this->where($this->makeByCondition(array('<>', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на конструкции LIKE
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * Значение может быть как строкой, так и массивом. В случае массива сформируется конструкция OR
     * @param array|string $field
     * @param array|string $needle
     * @return $this
     */
    public function whereLike($field, $needle)
    {
        return $this->where($this->makeByCondition(array('LIKE', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на конструкции NOT LIKE
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * Значение может быть как строкой, так и массивом. В случае массива сформируется конструкция OR
     * @param array|string $field
     * @param array|string $needle
     * @return $this
     */
    public function whereNotLike($field, $needle)
    {
        return $this->where($this->makeByCondition(array('NOT LIKE', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $needle)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на конструкции BETWEEN
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * @param array|string $field
     * @param string|int|float $start
     * @param string|int|float $end
     * @return $this
     */
    public function whereBetween($field, $start, $end)
    {
        return $this->where($this->makeByCondition(array('BETWEEN', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $start, self::SQL_OPERAND_VALUE, $end)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на конструкции NOT BETWEEN
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * Значение может быть как строкой, так и массивом. В случае массива сформируется конструкция OR
     * @param array|string $field
     * @param string|int|float $start
     * @param string|int|float $end
     * @return $this
     */
    public function whereNotBetween($field, $start, $end)
    {
        return $this->where($this->makeByCondition(array('NOT BETWEEN', self::SQL_OPERAND_FIELD, $field, self::SQL_OPERAND_VALUE, $start, self::SQL_OPERAND_VALUE, $end)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на конструкции IS NULL
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * @param array|string $field
     * @return $this
     */
    public function whereNull($field)
    {
        return $this->where($this->makeByCondition(array('IS NULL', self::SQL_OPERAND_FIELD, $field)));
    }

    /**
     * Добавляет в запрос условие выборки, основанное на конструкции IS NOT NULL
     * Имя поля может быть как строкой, так и массивом. В случае массива сформируется набор конструкций OR
     * @param array|string $field
     * @return $this
     */
    public function whereNotNull($field)
    {
        return $this->where($this->makeByCondition(array('IS NOT NULL', self::SQL_OPERAND_FIELD, $field)));
    }

    /**
     * Добавляет в запрос набор условий выборки,
     * основанных на равенстве полей, указанных в ключах входного ассоциативного массива,
     * значениям, указанным в значениях входного массива
     * @param array $ps
     * @return $this
     */
    public function whereFields($ps)
    {
        if (!is_array($ps)) {
            return $this;
        }

        foreach ($ps as $k => $v) {
            $this->whereBy($k, $v);
        }
        return $this;
    }

    /**
     * Добавляет в запрос условие группировки
     * @param string $table
     * @param string $field
     * @return $this
     */
    public function groupBy($table, $field = false)
    {
        if (empty($field)) {
            $this->groupBy[] = $table;
            return $this;
        }
        $this->groupBy[] = $table . '.' . $field;
        return $this;
    }

    /**
     * Добавляет в запрос произвольное условие типа HAVING
     * @param string $having
     * @return $this
     */
    public function having($having)
    {
        $this->having[] = $having;
        return $this;
    }

    /**
     * Добавляет в запрос объединение с другим запросом
     * @param mixed $sql
     * @return $this
     */
    public function union($sql)
    {
        $this->unions[] = [$sql, ''];
        return $this;
    }

    /**
     * Добавляет в запрос объединение с другим запросом
     * @param mixed $sql
     * @return $this
     */
    public function unionAll($sql)
    {
        $this->unions[] = [$sql, 'ALL'];
        return $this;
    }

    /**
     * Добавляет в запрос условие сортировки
     * @param string $orderBy
     * @param string $direction
     * @return $this
     */
    public function orderBy($orderBy, $direction = null)
    {
        if (!empty($direction)) {
            $direction = strtolower($direction);
            if (!in_array($direction, ['asc', 'desc'])) {
                $direction = 'asc';
            }

            if (!preg_match('/^[`\w_\.]+$/', $orderBy)) {
                return $this;
            }

            $cond = '';
            if (strpos($orderBy, '.') !== false) {
                $cond = $this->db->escape($orderBy) . ' ' . $direction;
            } else {
                $cond = $this->getAlias() . '.`' . $this->db->escape($orderBy) . '` ' . $direction;
            }

            if (in_array($cond, $this->orderBy)) {
                return $this;
            }

            $this->orderBy[] = $cond;
            return $this;
        }

        if (empty($orderBy) || in_array($orderBy, $this->orderBy)) {
            return $this;
        }

        $this->orderBy[] = $orderBy;
        return $this;
    }

    protected function orderByRaw($orderBy)
    {
        $this->orderBy[] = $orderBy;
        return $this;
    }

    /**
     * Добавляет в запрос условие на базе LIMIT
     * @param string|int $start
     * @param string|int $count
     * @return $this
     */
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

    /**
     * Добавляет в запрос набор условий, связанных с постраничной навигацией
     *
     * @param \Pina\Paging $paging
     * @param string $field название поля, по которому будет считаться COUNT()
     * @param bool $useJoin указывает, необходимо ли подключать другие таблицы через JOIN
     * @return $this
     */
    public function paging(&$paging, $field = false, $useJoin = true)
    {
        $paging->setTotal($this->pagingCount($field, $useJoin));

        $limitStart = intval($paging->getStart());
        $limitCount = intval($paging->getCount());
        $this->limit($limitStart, $limitCount);

        return $this;
    }

    /**
     * @deprecated
     */
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

    /**
     * Собирает и возвращает строку запроса, связанну с частью WHERE
     * @return string
     */
    public function makeWhere()
    {
        $sql = join(' AND ', $this->getWhereArray(true));

        if ($sql != '') {
            $sql = ' WHERE ' . $sql;
        }

        return $sql;
    }

    /**
     * Возвращает массив с подготовленным набором условий для конструкции WHERE
     * @return array
     */
    public function getWhereArray(bool $root)
    {
        $wheres = array();
        if ($root) {
            foreach ($this->context as $k => $v) {
                $wheres[] = $this->makeWhereBy($k, $v);
            }
        }

        foreach ($this->where as $where) {
            if (empty($where)) {
                continue;
            }

            $wheres[] = '(' . $where . ')';
        }

        return array_merge($wheres, $this->getJoinWhereArray());
    }

    /**
     * Сбрасывает все накопленные условия для конструкции WHERE
     * @return $this
     */
    public function resetWhere()
    {
        $this->where = [];
        return $this;
    }

    /**
     * Возвращает массив с подготовленным набором условий для конструкции WHERE,
     * полученным из присоединенных таблиц (JOIN)
     * @return array
     */
    public function getJoinWhereArray()
    {
        $wheres = array();
        foreach ($this->joins as $line) {
            if (count($line) == 2) {
                list($type, $table) = $line;
                $wheres = array_merge($wheres, $table->getWhereArray(false));
            }
        }
        return $wheres;
    }

    /**
     * Собирает и возвращает строку для конструкций типа JOIN
     * @return string
     */
    public function makeJoins()
    {
        $sql = '';
        foreach ($this->joins as $line) {
            list($type, $table) = $line;

            $joinSql = ' ' . $type . ' JOIN ';
            $joinSql .= $table->makeFrom();
            $joinSql .= $table->makeOns($this->getAlias());

            $joinSql .= $table->makeJoins();

            $sql .= $joinSql;
        }
        return $sql;
    }

    /**
     * Собирает и возвращает строку для конструкции GROUP BY
     * @return string
     */
    public function makeGroupBy()
    {
        if (empty($this->groupBy)) {
            return '';
        }
        return ' GROUP BY ' . join(', ', $this->groupBy);
    }

    /**
     * Собирает и возвращает строку для конструкции HAVING
     * @return string
     */
    public function makeHaving()
    {

        $sql = join(' AND ', $this->getHavingArray());

        if ($sql != '') {
            $sql = ' HAVING ' . $sql;
        }

        return $sql;
    }

    /**
     * Возвращает массив с подготовленным набором условий для конструкции HAVING
     * @return array
     */
    public function getHavingArray()
    {
        $havings = array();
        foreach ($this->having as $having) {
            if (empty($having)) {
                continue;
            }

            $havings[] = '(' . $having . ')';
        }

        return array_merge($havings, $this->getJoinHavingArray());
    }

    /**
     * Возвращает массив с подготовленным набором условий для конструкции HAVING,
     * полученным из присоединенных таблиц (JOIN)
     * @return array
     */
    public function getJoinHavingArray()
    {
        $havings = array();
        foreach ($this->joins as $line) {
            if (count($line) == 2) {
                list($type, $table) = $line;
                $havings = array_merge($havings, $table->getHavingArray());
            }
        }
        return $havings;
    }

    /**
     * Собирает и возвращает строку для конструкции ORDER BY
     * @return string
     */
    public function makeOrderBy()
    {
        $sql = join(', ', $this->orderBy);
        if (!empty($sql)) {
            $sql = ' ORDER BY ' . $sql;
        }

        return $sql;
    }

    /**
     * Собирает и возвращает строку для конструкции LIMIT
     * @return string
     */
    public function makeLimit()
    {
        $sql = '';
        if ($this->limitCount >= 0 && $this->limitStart !== null) {
            $sql .= ' LIMIT ' . $this->limitStart . ', ' . $this->limitCount;
        } elseif ($this->limitCount >= 0) {
            $sql .= ' LIMIT ' . $this->limitCount;
        }
        return $sql;
    }

    /**
     * Собирает и возвращает строку для конструкции UNION
     * @return string
     */
    public function makeUnions()
    {
        if (empty($this->unions)) {
            return '';
        }

        $sql = '';
        foreach ($this->unions as $union) {
            list($q, $type) = $union;
            $sql .= ' ' . trim('UNION' . ' ' . $type) . ' ';
            $sql .= strval($q);
        }

        return $sql;
    }

    /**
     * Собирает и возвращает строку со списком полей на выборку
     * @return string
     * @throws Exception
     */
    public function makeFields()
    {
        $fields = $this->getFieldArray();
        $sql = join(', ', $fields);

        if ($sql == '') {
            $sql = '*';
        }

        return $sql;
    }

    /**
     * Возвращает подготовленный массив со списком полей
     * @return array
     * @throws Exception
     */
    public function getFieldArray()
    {
        $fields = array();
        foreach ($this->select as $k => $v) {
            $type = array_shift($v);
            $field = array_shift($v);
            $alias = array_shift($v);
            switch ($type) {
                case self::SQL_SELECT_FIELD:
                    $fields[] = $this->getAlias() . '.' . $this->escapeField($field) . ($alias ? (' as ' . $this->escapeField($alias)) : '');
                    break;
                case self::SQL_SELECT_CONDITION:
                    $fields[] = $field . ($alias ? (' as `' . $alias . '`') : '');
                    break;
            }
        }
        $fields = array_merge($fields, $this->getJoinFieldArray());
        return $fields;
    }

    /**
     * @param $field
     * @return string
     * @throws Exception
     */
    protected function escapeField(string $field): string
    {
        if ($field == '*') {
            return $field;
        }

        $field = trim($field,'`');

        if (strpos($field, ',') !== false || strpos($field, '`') !== false) {
            throw new Exception('wrong field format');
        }

        return '`' . $field . '`';
    }

    /**
     * Возвращает подготовленный массив со списком полей из вложенных через JOIN таблиц
     * @return array
     */
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

    /**
     * Собирает и возвращает строку для конструкции COUNT
     * @param string $field
     * @return string
     */
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

    /**
     * Собирает и возвращает строку запроса
     * @return string
     */
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

        $sql .= $this->makeUnions();

        $sql .= $this->makeLimit();

        return $sql;
    }

    /**
     * Собирает и выводит текущее состояние запроса
     * @return $this
     */
    public function debug()
    {
        echo "\n".$this->make()."\n";
        return $this;
    }

    /**
     * Выполняет запрос и возвращает двумерный массив (таблицу) с набором записей
     * @return array
     */
    public function get()
    {
        return $this->db->table($this->make());
    }

    /**
     * Выполняет запрос и возвращает первую запись из выборки или NULL, если ничего не найдено
     * @return array|null
     */
    public function first()
    {
        $this->limit(1);

        return $this->db->row($this->make());
    }

    /**
     * Выполняет запрос и возвращает первую запись из выборки
     * Если запись не найдена, выбрасывает исключение
     * @return array
     * @throws NotFoundException
     */
    public function firstOrFail()
    {
        $line = $this->first();
        if (!isset($line)) {
            throw new NotFoundException;
        }
        return $line;
    }

    /**
     * Выполняет запрос и возвращает заданную ячейку из первой записи выборки
     * @param string $name
     * @param bool $useLimit
     * @return string|null
     */
    public function value($name, $useLimit = true)
    {
        if ($useLimit) {
            $this->limit(1);
        }

        $this->selectIfNotSelected($name);
        $this->forgetAllSelectedExcept([$name]);

        return $this->db->one($this->make());
    }

    /**
     * Выполняет запрос и возвращает заданную колонку $name из выборки.
     * Если задан второй параметр $key, то значения из этого поля станут ключами
     * выходного массива.
     * @param string $name
     * @param string $key
     * @return array
     */
    public function column($name, $key = null)
    {
        $this->selectIfNotSelected($name);
        if ($key) {
            $this->selectIfNotSelected($key);
        }
        $this->forgetAllSelectedExcept([$name, $key]);
        $sql = $this->make();
        $r = $key ? array_column($this->db->table($sql), $name, $key) : $this->db->col($sql);
        return $r;
    }

    public function forgetAllSelectedExcept(array $fields)
    {
        foreach ($this->select as $k => $v) {
            $type = array_shift($v);
            $field = array_shift($v);
            $alias = array_shift($v);

            $isMatch = $alias && in_array($alias, $fields) || in_array($field, $fields);
            if (!$isMatch) {
                unset($this->select[$k]);
            }
        }

        foreach ($this->joins as $k => $v) {
            $this->joins[$k][1]->forgetAllSelectedExcept($fields);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->make();
    }

    /**
     * Возвращает количества записей в выборке, чтобы
     * использовать их в методе paging.
     * @param string $field
     * @param bool $useJoin
     * @return string
     */
    public function pagingCount($field = false, $useJoin = true)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->makeCountFields($field);

        $sql .= ' FROM ' . $this->makeFrom();

        if (!empty($useJoin)) {
            $sql .= $this->makeJoins();
        }
        $sql .= $this->makeWhere();

        return $this->db->one($sql);
    }

    /**
     * Собирает, выполняет и возвращает результат запроса с COUNT заданного поля
     * @param string $field
     * @return int
     */
    public function count($field = false): int
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ';
        $sql .= $this->makeCountFields($field);

        $sql .= ' FROM ' . $this->getFrom();

        $sql .= $this->makeJoins();
        $sql .= $this->makeWhere();

        $sql .= $this->makeGroupBy();

        return intval($this->db->one($sql));
    }

    /**
     * Собирает, выполняет и возвращает результат запроса
     * с заданной агрегатной функцией
     * @param string $func
     * @param string $what
     * @return string
     */
    private function aggregate($func, $what)
    {
        if ($this->from == '') {
            return '';
        }

        $sql = 'SELECT ' . $func . '(' . $what . ')';
        $sql .= ' FROM ' . $this->makeFrom();

        $sql .= $this->makeJoins();
        $sql .= $this->makeWhere();
        $sql .= $this->makeGroupBy();

        return $this->db->one($sql);
    }

    /**
     * Собирает, выполняет и возвращает результат запроса MAX заданного поля
     * @param string $what
     * @return string
     */
    public function max($what)
    {
        return $this->aggregate('max', $what);
    }

    /**
     * Собирает, выполняет и возвращает результат запроса MIN заданного поля
     * @param string $what
     * @return string
     */
    public function min($what)
    {
        return $this->aggregate('min', $what);
    }

    /**
     * Собирает, выполняет и возвращает результат запроса AVG заданного поля
     * @param string $what
     * @return string
     */
    public function avg($what)
    {
        return $this->aggregate('avg', $what);
    }

    /**
     * Собирает, выполняет и возвращает результат запроса SUM заданного поля
     * @param string $what
     * @return string
     */
    public function sum($what)
    {
        return $this->aggregate('sum', $what);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return $this->limit(1)->count() > 0;
    }

    /**
     * Собирает строку запроса для конструкции SET
     * @param array $data
     * @param array|false $fields
     * @return boolean|string
     */
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

            if (is_null($value)) {
                $result .= $this->getAlias() . ".`" . $key . "` = NULL";
            } else {
                $result .= $this->getAlias() . ".`" . $key . "` = '" . $this->db->escape($value) . "'";
            }
        }
        if ($first) {
            return false;
        }

        return $result;
    }

    /**
     * Собирает и возвращает строку для конструкции ON BY, WHERE и т.д.
     * @param array $condition
     * @param string $parentAlias
     * @return string
     */
    public function makeByCondition($condition, $parentAlias = '')//$fields, $needle, $operand = '='
    {

        $operation = $condition[0];

        for ($i = 1; $i < count($condition); $i += 2) {
            $type = $condition[$i];
            $operand = $condition[$i + 1];
            $isOrCondition = is_array($operand) && ($type === self::SQL_OPERAND_FIELD || !in_array($operation, array('=', '<>', 'IN', 'NOT IN')));
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

    /**
     * Собирает и возвращает строку для бинарного оператора
     * @param array $condition
     * @param string $parentAlias
     * @return string
     */
    private function getBinaryCondition($condition, $parentAlias = '')
    {
        list($operation, $type1, $operand1, $type2, $operand2) = $condition;

        return $this->getOperand('', $type1, $operand1) . ' ' . $this->getOperand($operation, $type2, $operand2, $parentAlias);
    }

    /**
     * Собирает и возвращает строку для унарного оператора, где операция идет после операнда
     * @param array $condition
     * @param string $parentAlias
     * @return string
     */
    private function getUnaryPostfixCondition($condition, $parentAlias = '')
    {
        list($operation, $type1, $operand1) = $condition;

        return $this->getOperand('', $type1, $operand1) . ' ' . $operation;
    }

    /**
     * Собирает и возвращает строку для унарного оператора, где операция идете перед операндом
     * @param array $condition
     * @param string $parentAlias
     * @return string
     */
    private function getUnaryPrefixCondition($condition, $parentAlias = '')
    {
        list($operation, $type1, $operand1) = $condition;

        return $this->getOperand($operation, $type1, $operand1);
    }

    /**
     * Собирает и возвращает строку для оператора BETWEEN
     * @param array $condition
     * @param string $parentAlias
     * @return string
     */
    private function getBetweenCondition($condition, $parentAlias)
    {
        list($operation, $type1, $operand1, $type2, $operand2, $type3, $operand3) = $condition;

        return $this->getOperand('', $type1, $operand1) . ' ' . $this->getOperand($operation, $type2, $operand2, $parentAlias) . ' AND ' . $this->getOperand('', $type3, $operand3, $parentAlias);
    }

    /**
     * Собирает и возвращает строку для оператора
     * @param string $operation
     * @param string $type
     * @param array|string $operand
     * @param string $alias
     * @return string
     */
    private function getOperand($operation, $type, $operand, $alias = '')
    {
        if (is_array($operand) && $type === self::SQL_OPERAND_VALUE && empty($operation)) {
            //TODO: заменить выброс исключения фильтрацией параметров операнда и строгими типами операндов
            throw new Exception('unsupported format');
        }

        $prefix = $operation ? $operation . ' ' : '';
        if ($type === self::SQL_OPERAND_FIELD) {
            if (strpos($operand, '.')) {
                return $prefix . $operand;
            }
            return $prefix . ($alias ? '`' . trim($alias, '`') . '`' : $this->getAlias()) . '.`' . $operand . '`';
        }

        if (is_array($operand)) {
            if ($operation === '=') {
                return 'IN ' . $this->getInCondition($operand);
            } else if ($operation === '<>') {
                return 'NOT IN ' . $this->getInCondition($operand);
            } else if ($operation === 'IN' || $operation === 'NOT IN') {
                return $prefix . $this->getInCondition($operand);
            }

            //TODO: заменить выброс исключения фильтрацией параметров операнда и строгими типами операндов
            throw new Exception('bad array operation');
        }

        return $prefix . "'" . $this->db->escape($operand) . "'";
    }

    /**
     * Собирает и возвращает строку для оператора IN
     * @param array $needle
     * @return string
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

    /**
     * Собирает и выполняет запрос на вставку (INSERT)
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    public function insert($data, $fields = false)
    {
        $q = $this->makeInsert($data, $fields);
        if (empty($q)) {
            return false;
        }
        return $this->db->query($q);
    }

    /**
     * Собирает и выполняет запрос на вставку с игнорированием дубликатов (INSERT IGNORE)
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    public function insertIgnore($data, $fields = false)
    {
        $q = $this->makeInsert($data, $fields, $a = 'INSERT IGNORE');
        if (empty($q)) {
            return false;
        }
        return $this->db->query($q);
    }

    /**
     * Собирает и возвращает строку запроса на вставку
     * @param array $data
     * @param array|false $fields
     * @param string $cmd
     * @return string
     */
    public function makeInsert($data, $fields = false, $cmd = 'INSERT')
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if (!is_array(reset($data))) {
            return $cmd . " INTO " . $this->getFrom() . " SET " . $this->makeSetCondition($data, $fields);
        }

        list($keys, $values) = $this->getKeyValuesCondition($data, $fields);

        return $cmd . " INTO " . $this->getFrom() . "(`" . join("`,`", $keys) . "`) VALUES" . $values;
    }

    /**
     * Собирает и выполняет запрос на вставку (INSERT),
     * а также возвращает ID добавленной записи
     * @param array $data
     * @param array|false $fields
     * @return string
     */
    public function insertGetId($data, $fields = false)
    {
        return $this->insert($data, $fields) ? $this->db->insertId() : 0;
    }

    /**
     * Собирает и выполняет запрос на вставку с игнорированием дубликатов (INSERT IGNORE),
     * а также возвращает ID добавленной записи
     * @param array $data
     * @param array|false $fields
     * @return string
     */
    public function insertIgnoreGetId($data, $fields = false)
    {
        return $this->insertIgnore($data, $fields) ? $this->db->insertId() : 0;
    }

    /**
     * Собирает и выполняет запрос на замену (INSERT INTO .. ON DUPLICATE KEY UPDATE)
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    public function put($data, $fields = false)
    {
        $q = $this->makePut($data, $fields);
        if (empty($q)) {
            return false;
        }
        return $this->db->query($q);
    }

    /**
     * Собирает и возвращает строку запроса на замену (INSERT INTO .. ON DUPLICATE KEY UPDATE)
     * @param array $data
     * @param array|false $fields
     * @return string
     */
    public function makePut($data, $fields = false)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if (!is_array(reset($data))) {
            $set = $this->makeSetCondition($data, $fields);
            if (empty($set)) {
                return false;
            }

            $sql = "
                INSERT INTO " . $this->getFrom() . " SET " . $set . "
                ON DUPLICATE KEY UPDATE " . $set . "
            ";
            return $sql;
        }

        list($keys, $values) = $this->getKeyValuesCondition($data, $fields);
        $onDuplicate = $this->getOnDuplicateKeyCondition($keys);

        $sql = "INSERT INTO " . $this->getFrom() . "(`" . join("`,`", $keys) . "`) VALUES " . $values;
        if (!empty($onDuplicate)) {
            $sql .= " ON DUPLICATE KEY UPDATE " . $onDuplicate;
        }
        return $sql;
    }

    /**
     * Собирает и выполняет запрос на замену (INSERT INTO .. ON DUPLICATE KEY UPDATE)
     * а также возвращает ID добавленной записи
     * @param array $data
     * @param array|false $fields
     * @return string
     */
    public function putGetId($data, $fields = false)
    {
        return $this->put($data, $fields) ? $this->db->insertId() : 0;
    }

    /**
     * Собирает строку конструкции ON DUPLICATE KEY UPDATE
     * @param array $keys
     * @return string
     */
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
            $q .= '`' . $key . '`' . ' = VALUES(' . '`' . $key . '`' . ')';
        }
        return $q;
    }

    /**
     * @param array $keys
     * @return array
     */
    protected function getOnDuplicateKeys($keys)
    {
        return $keys;
    }

    /**
     * @param array $data
     * @param array|false $fields
     * @return array|false
     */
    protected function getKeyValuesCondition($data, $fields)
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

                if (!isset($line[$key]) || is_null($line[$key])) {
                    $sql_line .= "NULL";
                } else {
                    $sql_line .= "'" . $this->db->escape($line[$key]) . "'";
                }
            }
            $sql .= "(" . $sql_line . ")";
        }

        if (empty($sql)) {
            return false;
        }

        return array($keys, $sql);
    }

    /**
     * Собирает и выполняет запрос на обновление таблицы (UPDATE)
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    public function update($data, $fields = false)
    {
        $q = $this->makeUpdate($data, $fields);
        if (empty($q)) {
            return false;
        }
        return $this->db->query($q) ? $this->db->affectedRows() : 0;
    }

    /**
     * Собирает строку запроса на обновление таблицы (UPDATE)
     * @param array $data
     * @param array|false $fields
     * @return boolean
     */
    public function makeUpdate($data, $fields = false)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        return $this->makeUpdateOperation($this->makeSetCondition($data, $fields));
    }

    /**
     * Собирает и выполняет запрос на увеличение ячейки таблицы на заданное значение (UPDATE)
     * @param string $field
     * @param string $value
     * @return int
     */
    public function increment($field, $value)
    {
        return $this->updateOperation('`' . $field . '` = `' . $field . '` + ' . $this->db->escape($value));
    }

    /**
     * Собирает и выполняет запрос на уменьшение ячейки таблицы на заданное значение (UPDATE)
     * @param string $field
     * @param string $value
     * @return int
     */
    public function decrement($field, $value)
    {
        return $this->updateOperation('`' . $field . '` = `' . $field . '` - ' . $this->db->escape($value));
    }

    /**
     * Собирает и выполняет запрос на произвольное обновление таблицы (UPDATE)
     * @param string $operation
     * @return int
     */
    protected function updateOperation($operation)
    {
        $this->db->query($this->makeUpdateOperation($operation));
        return $this->db->affectedRows();
    }

    /**
     * Собирает и возвращает строку запроса на произвольное обновление таблицы (UPDATE)
     * @param string $operation
     * @return int
     */
    protected function makeUpdateOperation($operation)
    {
        if (empty($operation)) {
            return '';
        }
        return "UPDATE " . $this->getFrom() . " " . $this->makeJoins() . ' SET ' . $operation . $this->makeWhere() . $this->makeLimit();
    }

    /**
     * Собирает и выполняет запрос на удаление записей из таблицы (DELETE)
     * @param string $what
     * @return bool
     */
    public function delete($what = false)
    {
        return $this->db->query($this->makeDelete($what));
    }

    /**
     * Собирает и возвращает строку запроса на удаление записей из таблицы (DELETE)
     * @param string $what
     * @return bool
     */
    public function makeDelete($what = false)
    {
        $field = ($what ? ('`' . $what . '`') : '');
        if (empty($field) && count($this->joins)) {
            $field = $this->getAlias();
        }
        if (!empty($field)) {
            $field = ' ' . $field;
        }
        return "DELETE" . $field . " FROM " . $this->makeFrom() . $this->makeJoins() . $this->makeWhere()
            . $this->makeOrderBy() . $this->makeLimit();
    }

    /**
     * Выполняет запрос на обнуление таблицы (TRUNCATE)
     * @return bool
     */
    public function truncate()
    {
        return $this->db->query($this->makeTruncate());
    }

    /**
     * Собирает и возвращает строку запроса на обнуление таблицы (TRUNCATE)
     * @return bool
     */
    public function makeTruncate()
    {
        return "TRUNCATE " . $this->makeFrom();
    }

    /**
     * Запускает транзакцию
     * @return bool
     */
    public function startTransaction()
    {
        return $this->db->query("START TRANSACTION");
    }

    /**
     * Завершает транзакцию
     * @return bool
     */
    public function commit()
    {
        return $this->db->query("COMMIT");
    }

    /**
     * Откатывает транзакцию
     * @return bool
     */
    public function rollback()
    {
        return $this->db->query("ROLLBACK");
    }

}
