<?php

namespace Pina;

use Exception;
use Pina\Data\Schema;
use Pina\Data\SchemaExtension;
use Pina\DB\StructureParser;
use Pina\DB\Structure;
use Pina\SQL\DefinitionInterface;
use Pina\Types\TypeInterface;

/*
 * Базовый класс для работы с таблицами, содержит мета-информацию о таблицах
 * и базовые методы, наследуется от конструктора запросов
 *
 * @author Alex Yashin
 * @copyright 2015
 */

abstract class TableDataGateway extends SQL implements DefinitionInterface
{
    abstract public function getTable(): string;

    protected function getEngine(): string
    {
        return "InnoDB";
    }

    protected function getCharset(): string
    {
        if ($this->db->version() >= 8000) {
            return 'utf8mb3';
        }
        return 'utf8';
    }

    /**
     * Возвращает список триггеров
     * @return array
     */
    public function getTriggers()
    {
        return [];
    }

    /**
     * Возвращает список внешних ключей таблицы
     * @return array
     */
    public function getForeignKeys()
    {
        return [];
    }

    public function __construct()
    {
        parent::__construct($this);
    }

    /**
     * Возвращает схему таблицы
     * @return Schema
     */
    public function getSchema(): Schema
    {
        /** @var SchemaExtension $container */
        $container = App::load(SchemaExtension::class);
        return $container->get($this->getTable());
    }

    public static function addSchema(Schema $schema)
    {
        /** @var SchemaExtension $container */
        $container = App::load(SchemaExtension::class);
        $container->onGet(static::instance()->getTable(), function(Schema $base) use ($schema) {
            $base->addGroup($schema);
        });
    }

    public function getSource(): string
    {
        return '`' . $this->getTable() . '`';
    }

    /**
     * Возвращает список индексов
     * @return array
     */
    public function makeSQLIndexes(): array
    {
        return $this->getSchema()->makeSQLIndexes();
    }

    /**
     * Генерирует массив запросов на обновление структуры таблицы,
     * которые необходимо выполнить, чтобы привести состояние таблицы к описанному
     * в классе модели
     * @return array
     */
    public function getUpgrades()
    {
        if (empty($this->makeSQLFields()) || empty($this->getTable())) {
            return array(array(), array());
        }

        $first = array();
        $last = array();
        if (!in_array($this->getTable(), $this->db->col("SHOW TABLES"))) {
            $first[] = $this->getStructure()->makeCreateTable($this->getTable());
            $last[] = $this->getStructure()->makeCreateForeignKeys($this->getTable());
        } else {
            $first[] = $this->getStructure()->makeAlterTableDropForeignKeys($this->getTable(), $this->getExistedStructure());
            $first[] = $this->getStructure()->makeAlterTable($this->getTable(), $this->getExistedStructure());
            $first[] = $this->getStructure()->makeAlterTableCharset($this->getTable(), $this->getExistedStructure());
            $first[] = $this->getStructure()->makeAlterTableEngine($this->getTable(), $this->getExistedStructure());
            $last[] = $this->getStructure()->makeAlterTableAddForeignKeys($this->getTable(), $this->getExistedStructure());
        }
        return array(array_filter($first), array_filter($last));
    }

    /**
     * Разбирает структуру описания таблицы, представленную в классе модели,
     * и возвращает класс структуры
     * @return Structure
     */
    public function getStructure()
    {
        $parser = new StructureParser;
        $structure = new Structure;
        $structure->setFields($parser->parseGatewayFields($this->makeSQLFields()));
        $structure->setIndexes($parser->parseGatewayIndexes($this->makeSQLIndexes()));
        $structure->setForeignKeys($this->getForeignKeys());
        $structure->setEngine($this->getEngine());
        $structure->setCharset($this->getCharset());
        return $structure;
    }

    /**
     * Разбирает структуру таблицы, представленную в базе данных
     * и возвращает класс структуры
     * @return Structure
     */
    public function getExistedStructure()
    {
        $parser = new StructureParser;
        $data = $this->db->row("SHOW CREATE TABLE `" . $this->getTable() . "`");
        $parser->parse($data['Create Table']);
        return $parser->getStructure();
    }

    public function makeSQLFields(): array
    {
        return $this->getSchema()->makeSQLFields();
    }

    /**
     * Возвращает экземпляр конкретного класса
     * @return $this
     */
    public static function instance()
    {
        $cl = get_called_class();
        return App::make($cl);
    }

    /**
     * Проверяет, есть ли поле с заданным именем
     * @param string $field
     * @return bool
     */
    public function hasField($field)
    {
        return $this->getSchema()->hasReal($field);
    }

    public function hasAllFields(array $fields): bool
    {
        $count = count($fields);
        if ($count === 0) {
            return false;
        }
        $keys = $this->getSchema()->getRealFieldNames();
        return count(array_intersect($keys, $fields)) === $count;
    }

    /**
     * Добавляет к запросу условие по ID, выполняет его
     * и возвращает первую строку выборки
     * @param string|int $id
     * @return array|null
     */
    public function find($id)
    {
        return $this->whereId($id)->first();
    }

    /**
     * Добавляет к запросу условие по ID, выполняет его
     * и возвращает первую строку выборки
     * Если запись не найдена, то выбрасывает исключение
     * @param string|int $id
     * @return array
     * @throws NotFoundException
     */
    public function findOrFail($id)
    {
        $line = $this->find($id);
        if (!isset($line)) {
            throw new NotFoundException;
        }
        return $line;
    }

    /**
     * Выполняет запрос и возвращает значение первичного ключа для первой строки
     * @return string|int
     * @throws Exception
     */
    public function id()
    {
        return $this->value($this->singlePrimaryKeyField());
    }

    /**
     * Проводит подготовку массива под вставку:
     * - добавляет значения контекста
     * @param array $data
     */
    protected function adjustDataAndFields(&$data)
    {
        foreach ($this->context as $field => $value) {
            if (!isset($data[0])) {
                $data[$field] = $value;
            } else {
                foreach ($data as $k => $v) {
                    $data[$k][$field] = $value;
                }
            }
        }
    }

    /**
     * Возвращает массив полей первичного ключа
     * @return string[]
     */
    protected function getPrimaryKey(): array
    {
        return $this->getSchema()->getPrimaryKey();
    }

    public function getSinglePrimaryKey($context = [])
    {
        $pk = $this->getPrimaryKey();
        foreach ($context as $key => $value) {
            if (in_array($key, $pk)) {
                $this->whereBy($key, $value);
                $pk = array_diff($pk, [$key]);
            }
        }

        $singlePk = array_shift($pk);
        if (count($pk) > 0) {
            throw new Exception("Wrong primary key");
        }

        return $singlePk;
    }

    /**
     * Возвращает первое поля первичного ключа, если PK состоит из одного поля,
     * иначе выбрасывает исключение
     * @return string
     * @throws Exception
     */
    protected function singlePrimaryKeyField(): string
    {
        $pk = $this->getPrimaryKey();
        if (count($pk) == 1) {
            return $pk[0];
        }
        throw new Exception("Wrong primary key");
    }

    /**
     * Собирает и возвращает текст запроса на вставку данных в таблицу
     * @param array $data
     * @param string $cmd
     * @return string
     */
    public function makeInsert(array $data, string $cmd = 'INSERT', string $onDuplicate = ''): string
    {
        $this->adjustDataAndFields($data);

        return parent::makeInsert($data, $cmd, $onDuplicate);
    }

    /**
     * Собирает и возвращает текст запроса на замену данных в таблице
     * @param array $data
     * @param array $fields
     * @return string
     */
    public function makePut($data)
    {
        $this->adjustDataAndFields($data);
        return parent::makePut($data);
    }

    /**
     * Собирает и возвращает текст запроса на обновление данных в таблице
     * @param array $data
     * @return string
     */
    public function update($data)
    {
        if (empty($data)) {
            return false;
        }

        $this->adjustDataAndFields($data);
        return parent::update($data);
    }

    /**
     * Добавляет условие на соответствие ID заданному значению
     * @param array|string $id
     * @return $this
     * @throws Exception
     */
    public function whereId($id, array $context = [])
    {
        $pk = $this->getPrimaryKey();
        //Если PK состоит из нескольких частей, то все кроме одной должны быть переданы через $context
        foreach ($context as $key => $value) {
            if (in_array($key, $pk)) {
                $this->whereBy($key, $value);
                $pk = array_diff($pk, [$key]);
            }
        }

        $singlePk = array_shift($pk);

        if (count($pk) > 0) {
            throw new Exception("Wrong primary key");
        }

        return $this->whereBy($singlePk, $id);
    }

    /**
     * Добавляет в запрос условие на несоответствие ID заданному значению
     * @param array|string $id
     * @return $this
     * @throws Exception
     */
    public function whereNotId($id, $context = [])
    {
        $pk = $this->getPrimaryKey();
        //Если PK состоит из нескольких частей, то все кроме одной должны быть переданы через $context
        $condition = [];
        foreach ($context as $key => $value) {
            if (in_array($key, $pk)) {
                $condition[] = $this->makeWhereBy($key, $value);
                $pk = array_diff($pk, [$key]);
            }
        }

        $singlePk = array_shift($pk);

        if (count($pk) > 0) {
            throw new Exception("Wrong primary key");
        }

        $condition[] = $this->makeWhereBy($singlePk, $id);

        return $this->where('NOT (' . implode(' AND ', $condition) . ')');
    }

    public function selectAll()
    {
        $selectedFields = $this->getSchema()->getRealFieldNames();
        foreach ($selectedFields as $selectedField) {
            $this->select($selectedField);
        }
        return $this;
    }

    /**
     * Добавляет в запрос выборку всех полей кроме заданных
     * @param array|string $field
     * @return $this
     */
    public function selectAllExcept($field)
    {
        $excludedFields = is_array($field) ? $field : explode(",", $field);
        array_walk($excludedFields, 'trim');
        $selectedFields = array_diff($this->getSchema()->getRealFieldNames(), $excludedFields);
        foreach ($selectedFields as $selectedField) {
            $this->select($selectedField);
        }
        return $this;
    }

    /**
     * @param string $alias
     * @return $this
     * @throws Exception
     */
    public function selectId($alias = 'id')
    {
        return $this->selectAsIfNotSelected($this->singlePrimaryKeyField(), $alias);
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function selectTitle($alias = 'title')
    {
        return $this->selectAsIfNotSelected('title', $alias);
    }

    /**
     * Собирает и возвращает текст запроса, отвечающего за сортировку (для ORDER BY)
     * @param string $s
     * @return string
     */
    public function getSorting($s)
    {
        if (empty($this->sorts) || empty($s)) {
            return '';
        }

        $order = '';
        $ss = explode(',', $s);
        foreach ($ss as $v) {
            $v = trim($v);

            $isAsc = true;
            if ($v[0] == '-') {
                $isAsc = false;
                $v = trim(substr($v, 1));
            }

            if (empty($this->sorts[$v])) {
                continue;
            }

            if (!empty($order)) {
                $order .= ',';
            }
            $order .= $this->sorts[$v] . ' ' . ($isAsc ? 'asc' : 'desc');
        }

        if (empty($order)) {
            return '';
        }

        return $order;
    }

    /**
     * Добавляет в запрос условие сортировки
     * @param string $s
     * @return $this
     */
    public function sort($s)
    {
        return $this->orderBy($this->getSorting($s));
    }

    /**
     * Выполняет запрос на изменение порядка данных в таблице,
     * основываясь на порядке идентификаторов первичного ключа в массиве $ids
     * и имени поля $field, отвечающего за сортировку
     * @param array $ids
     * @param string $field
     * @return void
     */
    public function reorder($ids, $field = 'order')
    {
        if (!$this->getSchema()->hasReal($field)) {
            return;
        }

        $gw = clone($this);

        $orders = $gw->whereId($ids)->orderBy($field, 'asc')->column($field);

        $max = max($orders);

        $last = null;
        $diff = 0;
        foreach ($orders as $k => $v) {
            $orders[$k] = intval($v + $diff);
            if ($last !== null && $orders[$k] == $last) {
                $diff++;
                $orders[$k]++;
            }
            $last = $orders[$k];
        }

        if ($diff > 0) {
            $gw = clone($this);
            $gw->whereBetween($field, $max, 2147483647 - $diff - 1)->increment($field, $diff + 1);
        }
        $i = 0;
        foreach ($ids as $id) {
            $order = $orders[$i++];
            $gw = clone($this);
            $gw->whereId($id)->update([$field => intval($order)]);
        }
    }

    public function whereSearch($search, Schema $schema)
    {
        $search = trim($search);
        if (empty($search)) {
            return $this;
        }
        foreach ($schema->getIterator() as $field) {
            $type = App::type($field->getType());
            if (!$type->isSearchable()) {
                continue;
            }
            $table = $this->resolveFieldTable($field);
            if (!$table) {
                $table = $this;
            }
            if (!$table->getSchema()->hasReal($field->getName())) {
                continue;
            }
            $conditions[] = $table->makeByCondition(array('LIKE', self::SQL_OPERAND_FIELD, $field->getName(), self::SQL_OPERAND_VALUE, '%' . $search . '%'));
        }
        if (empty($conditions)) {
            return $this;
        }
        return $this->where(implode(' OR ', $conditions));
    }

    /**
     * @param array $filters
     * @param Schema $schema
     * @return $this
     * @throws Exception
     */
    public function whereFilters($filters, Schema $schema)
    {
        foreach ($filters as $filter => $value) {
            if (empty($value)) {
                continue;
            }

            foreach ($schema->getIterator() as $field) {
                //ищем совпадающее поле в схеме
                if (!$field->match($filter)) {
                    continue;
                }

                //пытаемся выявить таблицу на основе выбранных для текущего запроса полей
                $table = $this->resolveFieldTable($field);
                if ($table) {
                    /** @var TypeInterface $type */
                    $type = App::type($field->getType());
                    $type->filter($table, $field->getSourceKey(), $value);
                    continue;
                }

                /** @var TypeInterface $type */
                $type = App::type($field->getType());
                $type->filter($this, $field->getSourceKey(), $value);
            }
        }
        return $this;
    }

}
