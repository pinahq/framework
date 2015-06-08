<?php

namespace Pina;

class TableDataGateway extends SQL
{

    public $table = "";
    public $primaryKey = "";
    public $orderBy = "";
    public $fields = false;
    public $indexes = array();

    public $siteId = 0;
    public $accountId = 0;

    public $useSiteId = false;
    public $useAccountId = false;

    public $engine = "ENGINE=InnoDB DEFAULT CHARSET=utf8";

    public function __construct($siteId = false)
    {
        //TODO: make tables prefixes
        if (empty($this->primaryKey)) {
            $this->primaryKey = str_replace("cody_", "", $this->table) . "_id";
        }

        $db = DB::get();
        parent::__construct($this->table, $db);
        
        if ($siteId !== false) {
            $this->siteId = intval($siteId);
            $this->accountId = Site::accountId($siteId);
        } else {
            $this->siteId = Site::id();
            $this->accountId = Site::accountId();
        }
        
        if ($this->useSiteId) {
            $this->whereBy('site_id', $this->siteId);
        }

        if ($this->useAccountId) {
            $this->whereBy('account_id', $this->accountId);
        }
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

        if ($this->useSiteId) {
            $fields [] = 'site_id';
            if (!isset($data[0])) {
                $data['site_id'] = $this->siteId;
            } else {
                foreach ($data as $k => $v) {
                    $data[$k]['site_id'] = $this->siteId;
                }
            }
        }

        if ($this->useAccountId) {
            $fields [] = 'account_id';
            if (!isset($data[0])) {
                $data['account_id'] = $this->accountId;
            } else {
                foreach ($data as $k => $v) {
                    $data[$k]['account_id'] = $this->accountId;
                }
            }
        }
    }

    public function insert($data = array(), $fields = false)
    {
        $this->adjustDataAndFields($data, $fields);

        return parent::insert($data, $fields);
    }
    
    public function insertGetId($data = array(), $fields = false)
    {
        $this->adjustDataAndFields($data, $fields);
        return parent::insertGetId($data, $fields);
    }

    public function put($data, $fields = false)
    {
        $this->adjustDataAndFields($data, $fields);
        return parent::put($data, array_keys($this->fields));
    }
    
    public function putGetId($data, $fields = false)
    {
        $this->adjustDataAndFields($data, $fields);
        return parent::putGetId($data, array_keys($this->fields));
    }

    public function update($data, $fields = false)
    {
        if (empty($data)) {
            return false;
        }
        
        $this->adjustDataAndFields($data, $fields);
        return parent::update($data, array_keys($this->fields));
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
}
