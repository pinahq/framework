<?php

namespace Pina;

use Pina\DB\StructureParser;
use Pina\DB\Structure;

/*
 * Базовый класс для работы с таблицами, содержит мета-информацию о таблицах 
 * и базовые методы, наследуется от конструктора запросов
 * 
 * @author Alex Yashin
 * @copyright 2015
 */

class TableDataGateway extends SQL
{

    const LOAD_BUFFER_LIMIT = 1024000;

    protected static $table = "";
    protected static $fields = false;
    protected static $indexes = [];
    protected static $engine = "ENGINE=InnoDB DEFAULT CHARSET=utf8";
    protected $orderBy = "";
    protected $context = array();

    /**
     * Возвращает список триггеров
     * @return array
     */
    public function getTriggers()
    {
        return array();
    }
    
    /**
     * Возвращает список внешних ключей таблицы
     * @return array
     */
    public function getForeignKeys()
    {
        return array();
    }

    public function __construct()
    {
        parent::__construct($this->getTable());
    }

    /**
     * Возвращает название таблицы
     * @return string
     */
    public function getTable()
    {
        return static::$table;
    }

    /**
     * Возвращает список полей
     * @return array
     */
    public function getFields()
    {
        return static::$fields;
    }

    /**
     * Возвращает список индексов
     * @return array
     */
    public function getIndexes()
    {
        return static::$indexes;
    }

    /**
     * Возвращает тип движка таблицы
     * @return string
     */
    public function getEngine()
    {
        return static::$engine;
    }

    /**
     * Генерирует массив запросов на обновление структуры таблицы, 
     * которые необходимо выполнить, чтобы привести состояние таблицы к описанному
     * в классе модели
     * @return array
     */
    public function getUpgrades()
    {
        if (empty(static::$fields)) {
            return array(array(), array());
        }

        $first = array();
        $last = array();
        if (!in_array(static::$table, $this->db->col("SHOW TABLES"))) {
            $first[] = $this->getStructure()->makeCreateTable($this->getTable());
            $last[] = $this->getStructure()->makeCreateForeignKeys($this->getTable());
        } else {
            $first[] = $this->getStructure()->makeAlterTable($this->getTable(), $this->getExistedStructure());
            $last[] = $this->getStructure()->makeAlterTableForeignKeys($this->getTable(), $this->getExistedStructure());
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
        $structure->setFields($parser->parseGatewayFields($this->getFields()));
        $structure->setIndexes($parser->parseGatewayIndexes($this->getIndexes()));
        $structure->setForeignKeys($this->getForeignKeys());
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

    /**
     * Возвращает набор корректных вариантов для поля типа ENUM на основании
     * анализа описания этого поля
     * @param string $field
     * @return array
     */
    public function getEnumVariants($field)
    {
        if (empty(static::$fields[$field])) {
            return [];
        }

        $meta = static::$fields[$field];
        if (!preg_match('/enum\((.*)\)/si', $meta, $matches)) {
            return [];
        }

        $fields = explode(',', $matches[1]);
        array_walk($fields, function(&$s) {
            $s = trim(trim($s, "'\""));
        });
        return $fields;
    }

    /**
     * Возвращает экземпляр конкретного класса
     * @return $this
     */
    public static function instance()
    {
        $cl = get_called_class();
        return new $cl();
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
        return $this->whereBy($field, $value);
    }

    /**
     * Проверяет, есть ли поле с заданным именем
     * @param string $field
     * @return bool
     */
    public function hasField($field)
    {
        return isset(static::$fields[$field]);
    }

    /**
     * Добавляет к запросу условие по ID, выполняет его
     * и возвращает первую строку выборки
     * @param string|int $id
     * @return array
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
     * @throws \Pina\NotFoundException
     */
    public function findOrFail($id)
    {
        $line = $this->find($id);
        if (!isset($line)) {
            throw new \Pina\NotFoundException;
        }
        return $line;
    }

    /**
     * Выполняет запрос и возвращает значение первичного ключа для первой строки
     * @return string|int
     */
    public function id()
    {
        return $this->value($this->primaryKey());
    }

    /**
     * Проводит подготовку массива под вставку:
     * - убирает лишние поля (которых нет в параметре $fields или в поле $field)
     * - добавляет значения контекста
     * @param array $data
     * @param array $fields
     */
    protected function adjustDataAndFields(&$data, &$fields)
    {
        if (!empty($fields)) {
            $fields = array_intersect($fields, array_keys(static::$fields));
        } else {
            $fields = array_keys(static::$fields);
        }

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
     * Возвращает названия поля первичного ключа
     * Если первичный ключ составной, возвращает название первого поля 
     * первичого ключа
     * @return string
     */
    protected function primaryKey()
    {
        if (empty(static::$indexes['PRIMARY KEY'])) {
            return '';
        }

        if (is_array(static::$indexes['PRIMARY KEY'])) {
            return static::$indexes['PRIMARY KEY'][0];
        }

        return static::$indexes['PRIMARY KEY'];
    }

    /**
     * Возвращает список ключей на обновление в случае, если будут дубликаты
     * По сути выбирает все имена полей кроме первичного ключа
     * @param array $keys
     * @return array
     */
    protected function getOnDuplicateKeys($keys)
    {
        $primaryKeys = !empty($this->indexes['PRIMARY KEY']) ? $this->indexes['PRIMARY KEY'] : array();
        if (!is_array($primaryKeys)) {
            $primaryKeys = array($primaryKeys);
        }

        return array_diff($keys, $primaryKeys);
    }

    /**
     * Собирает и возвращает текст запроса на вставку данных в таблицу
     * @param array $data
     * @param array $fields
     * @param string $cmd
     * @return string
     */
    public function makeInsert($data = array(), $fields = false, $cmd = 'INSERT')
    {
        $this->adjustDataAndFields($data, $fields);

        return parent::makeInsert($data, $fields, $cmd);
    }

    /**
     * Собирает и возвращает текст запроса на замену данных в таблице
     * @param array $data
     * @param array $fields
     * @return string
     */
    public function makePut($data, $fields = false)
    {
        $this->adjustDataAndFields($data, $fields);
        return parent::makePut($data, $fields);
    }

    /**
     * Собирает и возвращает текст запроса на обновление данных в таблице
     * @param array $data
     * @param array $fields
     * @return string
     */
    public function update($data, $fields = false)
    {
        if (empty($data)) {
            return false;
        }

        $this->adjustDataAndFields($data, $fields);
        return parent::update($data, $fields);
    }

    /**
     * Добавляет условие на соответствие ID заданному значению
     * @param string|int $id
     * @return $this
     */
    public function whereId($id)
    {
        return $this->whereBy($this->primaryKey(), $id);
    }

    /**
     * Добавляет в запрос условие на несоответствие ID заданному значению
     * @param string|int $id
     * @return $this
     */
    public function whereNotId($id)
    {
        return $this->whereNotBy($this->primaryKey(), $id);
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
        $selectedFields = array_diff(array_keys(static::$fields), $excludedFields);
        foreach ($selectedFields as $selectedField) {
            $this->select($selectedField);
        }
        return $this;
    }

    /**
     * @deprecated
     * @return $this
     */
    public function enabled()
    {
        $prefix = str_replace("cody_", "", $this->table);

        return $this->whereBy($prefix . "_enabled", 'Y');
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
        foreach ($ss as $k => $v) {
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
        if (!isset(static::$fields[$field])) {
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
                $diff ++;
                $orders[$k] ++;
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
    
    /**
     * Проверяет входной массив на соответствие требованиям типа данных полей таблицы.
     * Возвращает список ошибок.
     * @param array $data
     * @return array
     */
    public function validate($data)
    {
        $errors = [];
        foreach ($data as $k => $v) {
            $matches = array();
            if (preg_match("/(varchar|decimal)\((\d+)(,(\d+))?\)/i", static::$fields[$k], $matches)) {
                $length = 0;
                $type = strtolower($matches[1]);
                $maxLength = strtolower($matches[2]);
                switch ($type) {
                    case 'varchar':
                        $length = strlen($v);
                        break;
                    case 'decimal':
                        $length = strlen(floor(abs($v)));
                        break;
                }
                if ($maxLength >= $length) {
                    continue;
                }
                $errors[] = [
                    'length',
                    $k,
                    $maxLength,
                    $length,
                ];
            } else if ($variants = $this->getEnumVariants($k)) {
                if (!in_array($v, $variants)) {
                    $errors[] = [
                        'enum',
                        $k,
                        $variants,
                        $v,
                    ];
                }
            }
        }
        return $errors;
    }

    /**
     * Загружает данные из объекта читателя согласно схеме
     * $schema = array("file_field" => "table_field");
     * 
     * @param array $schema
     * @param object $reader
     * @return int
     */
    public function load($schema, $reader)
    {
        $cnt = 0;
        $buffer = '';
        $data = $reader->fetch();
        foreach ($data as $line) {
            $prepared = [];
            foreach ($schema as $sourceKey => $targetKey) {
                if (isset($line[$sourceKey])) {
                    $prepared[$targetKey] = $line[$sourceKey];
                }
            }
            list($ks, $valueCondition) = $this->getKeyValuesCondition($prepared, $schema);
            if (strlen($values) > self::LOAD_BUFFER_LIMIT) {
                $this->db->query("REPLACE INTO `" . $this->from . "` " . join($schema) . " VALUES " . $buffer);
                $cnt += $this->db->affectedRows();
            }
            $buffer .= $valueCondition;
        }
        if (!empty($buffer)) {
            $this->db->query("REPLACE INTO `" . $this->from . "` " . join($schema) . " VALUES " . $buffer);
            $cnt += $this->db->affectedRows();
        }
        return $cnt;
    }

    /**
     * Загружает CSV из файла по указанному пути согласно схеме
     * @param array $schema
     * @param string $path
     * @return int
     */
    public function loadCSV($schema, $path)
    {
        $csv = \League\Csv\Reader::createFromPath($path);
        return $this->load($schema, $csv);
    }

}
