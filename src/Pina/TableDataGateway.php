<?php

namespace Pina;

use League\Csv\Reader;

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

    public $table = "";
    public $primaryKey = "";
    public $orderBy = "";
    public $fields = false;
    public $indexes = array();
    protected $context = array();
    public $engine = "ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function getTriggers()
    {
        return array();
    }

    public function __construct()
    {
        //TODO: make tables prefixes
        if (empty($this->primaryKey)) {
            $this->primaryKey = str_replace("cody_", "", $this->table) . "_id";
        }

        $db = DB::get();
        parent::__construct($this->table, $db);
    }

    public function getUpgrades()
    {
        if (empty($this->fields)) {
            return array();
        }

        $r = array();

        $upgrade = new TableDataGatewayUpgrade($this);
        $tables = $this->db->col("SHOW TABLES");
        if (!in_array($this->table, $tables)) {
            $r [] = $upgrade->makeCreateTable();
        } else if ($q = $upgrade->makeAlterTable()) {
            $r [] = $q;
        }
        $r = array_merge($r, $upgrade->getTriggerDiff());
        return $r;
    }

    public function doUpgrades()
    {
        $upgrades = $this->getUpgrades();
        if (empty($upgrades)) {
            return array();
        }
        foreach ($upgrades as $q) {
            if (empty($q)) {
                continue;
            }
            $this->db->query($q);
        }
        return $upgrades;
    }

    /*
     * Возвращает экземпляр конкретного класса
     * @return TableDataGateway
     */

    static public function instance()
    {
        $cl = get_called_class();
        return new $cl();
    }

    public function context($field, $value)
    {
        $this->context[$field] = $value;
        return $this->whereBy($field, $value);
    }

    public function hasField($field)
    {
        return isset($this->fields[$field]);
    }

    public function find($id)
    {
        return $this->whereId($id)->first();
    }

    public function id()
    {
        return $this->value($this->primaryKey);
    }

    protected function adjustDataAndFields(&$data, &$fields)
    {
        if (!empty($fields)) {
            $fields = array_intersect($fields, array_keys($this->fields));
        } else {
            $fields = array_keys($this->fields);
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

    protected function getOnDuplicateKeys($keys)
    {
        $primaryKeys = !empty($this->indexes['PRIMARY KEY']) ? $this->indexes['PRIMARY KEY'] : array();
        if (!is_array($primaryKeys)) {
            $primaryKeys = array($primaryKeys);
        }

        return array_diff($keys, $primaryKeys);
    }

    public function makeInsert($data = array(), $fields = false, $cmd = 'INSERT')
    {
        $this->adjustDataAndFields($data, $fields);

        return parent::makeInsert($data, $fields, $cmd);
    }

    public function makePut($data, $fields = false)
    {
        $this->adjustDataAndFields($data, $fields);
        return parent::makePut($data, $fields);
    }

    public function update($data, $fields = false)
    {
        if (empty($data)) {
            return false;
        }

        $this->adjustDataAndFields($data, $fields);
        return parent::update($data, $fields);
    }

    public function whereId($id)
    {
        return $this->whereBy($this->primaryKey, $id);
    }

    public function enabled()
    {
        $prefix = str_replace("cody_", "", $this->table);

        return $this->whereBy($prefix . "_enabled", 'Y');
    }

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

    public function sort($s)
    {
        return $this->orderBy($this->getSorting($s));
    }

    /**
     * Возвращает массив из возможных значений поля типа enum
     * @param string $field название поля
     * @return array массив возможных значений
     */
    public function reportFieldVariants($field)
    {
        $values = array();
        if (!isset($this->fields) || empty($this->fields[$field])) {
            return $values;
        }
        if (($firstPos = mb_strpos($this->fields[$field], '(')) === false || ($lastPos = mb_strpos($this->fields[$field], ')')) === false
        ) {
            return false;
        }
        $str = mb_substr($this->fields[$field], ++$firstPos, ($lastPos - $firstPos));
        return explode(',', str_replace(array("'", '"'), '', $str));
    }

    /* Проверяет поля массива $data
     * на соответствие размерности полей БД mysql.
     * При несоответствии помещает сообщения в Request::error
     * $relations - массив вида ('поле в БД' => 'поле в HTML-форме' */

    public function validate($data, $relations)
    {
        foreach ($data as $k => $v) {
            $matches = array();
            if (!preg_match("/(varchar|int|decimal)\((\d+)(,(\d+))?\)/i", $this->fields[$k], $matches)) {
                continue;
            }
            $sql_type = $matches[1];
            $sql_size = $matches[2];
            switch ($sql_type) {
                case 'varchar':
                    $data_length = strlen($v);
                    break;
                case 'int':
                case 'decimal':
                    $data_length = strlen(floor(abs($v)));
                    break;
            }
            if ($sql_size >= $data_length) {
                continue;
            }
            if (empty($relations[$k])) {
                Request::error(
                    'Максимальная длина параметра превышена на '
                    . ($data_length - $sql_size), $k
                );
            } else {
                Request::error(
                    'Максимальная длина параметра превышена на '
                    . ($data_length - $sql_size), $relations[$k]
                );
            }
        }
    }

    /*
     * $schema = array("file_field" => "table_field");
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

    public function loadCSV($schema, $path)
    {
        $csv = \League\Csv\Reader::createFromPath($path);
        return $this->load($schema, $csv);
    }

}
