<?php

namespace Pina;

use Pina\TableStructureParser;

class TableDataGatewayUpgrade
{

    public function __construct($gw)
    {
        $this->gateway = $gw;
        $this->db = $gw->db;
    }

    public function makeFieldCondition($field)
    {
        $gatewayFields = $this->gateway->getFields();

        if (!is_array($gatewayFields[$field])) {
            return $gatewayFields[$field];
        }

        $constructField = $gatewayFields[$field]['Type'] .
            ($gatewayFields[$field]['Null'] == 'NO' ? ' NOT NULL ' : ' NULL ');

        if (isset($gatewayFields[$field]['Default']) && $gatewayFields[$field]['Default'] !== '') {
            if ($gatewayFields[$field]['Default'] === 'CURRENT_TIMESTAMP') {
                $constructField .= 'DEFAULT CURRENT_TIMESTAMP ';
            } else {
                $constructField .= "DEFAULT '" . $gatewayFields[$field]['Default'] . "'";
            }
        }

        if (isset($gatewayFields[$field]['Extra']) && $gatewayFields[$field]['Extra'] !== '') {
            $constructField .= $gatewayFields[$field]['Extra'];
        }

        return $constructField;
    }

    public function makeCreateTable()
    {
        $gatewayFields = $this->gateway->getFields();
        $gatewayIndexes = $this->gateway->getIndexes();
        $engine = $this->gateway->getEngine();

        if (isset($gatewayFields[0]) || empty($engine)) {
            return false;
        }

        $q = "CREATE TABLE IF NOT EXISTS `" . $this->gateway->getTable() . "` (";
        foreach ($gatewayFields as $field => $params) {
            $q .= '`' . $field . '` ' . $this->makeFieldCondition($field) . ', ';
        }
        foreach ($gatewayIndexes as $index => $params) {
            $q .= $this->makeIndex($index) . ', ';
        }
        $q = rtrim($q, ', ') . ') ' . $engine . ';';

        return $q;
    }

    public function parseFieldDescription($descr)
    {
        preg_match(
            "/" .
            "(\w+(\(.*\)(\s+UNSIGNED)?)?)" .
            "(\s+(NOT NULL|NULL))?" .
            "(\s+DEFAULT\s+'?([^']*)'?)?" .
            "(\s+AUTO_INCREMENT)?" .
            "/i", $descr, $matches
        );

        if (empty($matches[1])) {
            return false;
        }

        $type = strtolower($matches[1]);
        $null = (!empty($matches[5]) && strcasecmp($matches[5], "NOT NULL") == 0) ? "NO" : "YES";
        $default = isset($matches[7]) ? $matches[7] : '';
        $extra = !empty($matches[8]) ? strtolower(trim($matches[8])) : '';

        if (strcasecmp($default, 'null') === 0) {
            $default = null;
        }

        if (preg_match("/(\s+ON UPDATE\s+'?([^']*)'?)/i", $default, $matches)) {
            $default = str_replace($matches[0], '', $default);
            $extra = strtolower(trim($matches[0])) . (!empty($extra) ? (' ' . $extra) : '');
        }

        return ["Type" => $type, "Null" => $null, "Default" => $default, "Extra" => $extra];
    }

    public function makeCreateIndexesDescription($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        $joined = array();
        foreach ($data as $item) {
            $title = '';
            if ($item["Key_name"] == "PRIMARY") {
                $title .= "PRIMARY KEY";
            } elseif ($item["Index_type"] == "FULLTEXT") {
                $title .= "FULLTEXT " . $item["Key_name"];
            } elseif ($item["Non_unique"] == 0) {
                $title .= "UNIQUE KEY " . $item["Key_name"];
            } elseif ($item["Non_unique"] == 1) {
                $title .= "KEY " . $item["Key_name"];
            }

            if (empty($joined[$title])) {
                $joined[$title] = $item["Column_name"];
            } elseif (is_array($joined[$title])) {
                $joined[$title][] = $item["Column_name"];
            } else {
                $joined[$title] = array($joined[$title], $item["Column_name"]);
            }
        }

        return $joined;
    }

    public function diff()
    {
        $gatewayFields = $this->gateway->getFields();
        if (empty($gatewayFields) || isset($gatewayFields[0])) {
            return false;
        }

        $diff = array();
        $fieldParams = $this->db->table("SHOW COLUMNS FROM `" . $this->gateway->getTable() . "`");
        $fields = array();
        foreach ($fieldParams as $params) {
            $params['Type'] = strtolower($params['Type']);
            $params['Extra'] = strtolower($params['Extra']);

            $fields[] = $params['Field'];

            if (!isset($gatewayFields[$params['Field']])) {
                $diff['delete_fields'][] = $params['Field'];
                continue;
            }

            $structured = $gatewayFields[$params['Field']];

            if (!is_array($structured)) {
                $structured = $this->parseFieldDescription($gatewayFields[$params['Field']]);
            }

            if (count(array_diff($structured, $params))) {
                $diff['edit_fields'][] = $params['Field'];
            }
        }

        if ($addFields = array_diff(array_keys($gatewayFields), $fields)) {
            $diff['add_fields'] = $addFields;
        }

        $gatewayIndexes = $this->gateway->getIndexes();
        $indexesParams = $this->db->table("SHOW INDEXES FROM `" . $this->gateway->getTable() . "`");
        $indexesParams = $this->makeCreateIndexesDescription($indexesParams);
        $indexes = array();
        if (is_array($indexesParams)) {
            foreach ($indexesParams as $title => $columns) {
                $indexes[] = $title;
                if (!isset($gatewayIndexes[$title])) {
                    $diff['delete_indexes'][] = $title;
                    continue;
                }

                $index = $gatewayIndexes[$title];
                if (!is_array($index)) {
                    $index = array($index);
                }
                if (!is_array($columns)) {
                    $columns = array($columns);
                }

                if (count(array_diff($index, $columns)) || count(array_diff($columns, $index))) {
                    $diff['edit_indexes'][] = $title;
                    continue;
                }
            }
        }
        if ($addIndexes = array_unique(array_diff(array_keys($gatewayIndexes), $indexes))) {
            $diff['add_indexes'] = $addIndexes;
        }

        $tableCondition = $this->db->one("SHOW CREATE TABLE `" . $this->gateway->getTable() . "`");
        $databaseConstraints = (new Parser($tableCondition))->getConstraints();
        $gatewayContraints = $this->gw->getConstraints();
        $names = array();
        if (!empty($databaseConstraints)) {
            foreach ($databaseConstraints as $name => $constraint) {
                $names[] = $name;
                if (!isset($gatewayContraints[$name])) {
                    $diff['delete_constraints'][] = $name;
                }

                $foreignKey = $gatewayContraints[$name];
                if ($foreignKey->make($name) != $constraint->make($name)) {
                    $diff['edit_constraints'][] = $name;
                }
            }
        }

        if ($addConstraints = array_unique(array_diff(array_keys($gatewayContraints), $names))) {
            $diff['add_constraints'] = $addConstraints;
        }

        foreach ($diff as $key => $value) {
            $diff[$key] = array_unique($value);
        }

        return $diff;
    }

    public function makeIndex($indexName)
    {
        $gatewayIndexes = $this->gateway->getIndexes();

        $index = '';
        if (is_array($gatewayIndexes[$indexName])) {
            foreach ($gatewayIndexes[$indexName] as $field) {
                if (!empty($index)) {
                    $index .= ',';
                }
                $index .= "`" . $field . '`';
            }
        } else {
            $index .= "`" . $gatewayIndexes[$indexName] . '`';
        }
        return $indexName . '(' . $index . ')';
    }

    public function makeConstraint($indexName)
    {
        $gatewayConstraints = $this->gateway->getConstraints();

        if (!isset($gatewayConstraints[$indexName])) {
            throw new Exception('Can`t find constraint');
        }
        return $gatewayConstraints[$indexName]->make($indexName);
    }

    public function constructChanges($type, $data)
    {
        $q = '';
        foreach ($data as $k => $v) {
            switch ($type) {
                case 'add_fields':
                    $q .= ' ADD COLUMN `' . $v . '` ' . $this->makeFieldCondition($v) . ', ';
                    break;
                case 'delete_fields':
                    $q .= ' DROP COLUMN `' . $v . '`, ';
                    break;
                case 'edit_fields':
                    $q .= ' MODIFY `' . $v . '` ' . $this->makeFieldCondition($v) . ', ';
                    break;
                case 'add_indexes':
                    $q .= ' ADD ' . $this->makeIndex($v) . ', ';
                    break;
                case 'delete_indexes':
                    $q .= ' DROP ' . str_replace(["UNIQUE", "FULLTEXT"], ["", "KEY"], $v) . ', ';
                    break;
                case 'edit_indexes':
                    $q .= ' DROP ' . str_replace("UNIQUE", "", $v) . ', ADD ' . $this->makeIndex($v) . ', ';
                    break;
                case 'add_constraints':
                    $q .= ' ADD ' . $this->makeConstaint($v) . ', ';
                    break;
                case 'delete_indexes':
                    $q .= ' DROP FOREIGN KEY `' . $v . '`, ';
                    break;
                case 'edit_constraints':
                    $q .= ' DROP FOREIGN KEY `' . $v . '`, ADD ' . $this->makeConstraint($v) . ', ';
                    break;
                default:
                    return false;
                    break;
            }
        }

        return $q;
    }

    public function makeAlterTable()
    {
        $data = $this->diff();
        if (!is_array(@$data) || !count(@$data)) {
            return false;
        }

        $q = "ALTER TABLE `" . $this->gateway->getTable() . "` ";
        foreach ($data as $type => $value) {
            $q .= $this->constructChanges($type, $value);
        }
        $q = trim($q, ', ') . ';';

        return $q;
    }

}
