<?php

namespace Pina;

class TableDataLoader
{

    public $inputCoding = 'cp1251';
    public $outputCoding = 'UTF-8';
    public $file = false;
    public $db;
    public $fields = array();
    public $table = '';

    function __construct()
    {
        $this->db = DB::get();
    }

    public function setDir($dir)
    {
        $this->dir = $this->dir . $dir;
    }

    protected function getDir()
    {
        return $this->dir;
    }

    public function setInputCoding($coding)
    {
        $this->inputCoding = $coding;
    }

    public function setOutputCoding($coding)
    {
        $this->outputCoding = $coding;
    }

    public function constructFieldsCondition($data)
    {
        $result = '';
        foreach ($data as $k => $v) {
            if (is_array($this->fields) && !in_array($v, $this->fields)) {
                continue;
            }

            if (!empty($result))
                $result .= ',';

            $result .= '`' . $v . '`';
        }
        return '(' . $result . ')';
    }

    public function constructValuesCondition($data)
    {
        $result = '';
        foreach ($data as $k => $v) {
            if (!empty($result))
                $result .= ',';

            if ($this->fields[$k] == "site_id") {
                $v = Site::id();
            }

            $result .= "'" . $this->db->escape($v) . "'";
        }
        return '(' . $result . ')';
    }

    protected function myIconv($data)
    {
        if (!is_array($data) || $this->inputCoding == 'UTF-8')
            return $data;
        foreach ($data as $key => $str) {
            $data[$key] = iconv($this->inputCoding, $this->outputCoding, $data[$key]);
        }
        return $data;
    }

    public function import()
    {
        $fp = fopen($this->file, 'r');
        if (!$fp)
            continue;

        $i = 0;
        $values = "";
        while (($row = fgetcsv($fp, 0, ";")) !== FALSE) {
            if (!isset($row[2]) || empty($row[2]) || count($this->fields) != count($row)) {
                continue;
            }

            if ($values)
                $values .= ",";
            $values .= $this->constructValuesCondition($this->myIconv($row));

            if (strlen($values) > 1024000) {
                $this->db->query("REPLACE INTO `" . $this->table . "` " . $this->constructFieldsCondition($this->fields) . " VALUES " . $values);
                $values = "";
            }
            $i++;
        }
        fclose($fp);

        if ($values) {
            $this->db->query("REPLACE INTO `" . $this->table . "` " . $this->constructFieldsCondition($this->fields) . " VALUES " . $values);
        }
        return $i;
    }

}
