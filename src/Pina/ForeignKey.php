<?php

namespace Pina;

class ForeignKey
{

    protected $column = null;
    protected $table = null;
    protected $key = null;
    protected $onDelete = '';
    protected $onUpdate = '';

    public function __construct($column)
    {
        $this->column = $column;
    }

    public function references($table, $key)
    {
        $this->table = $table;
        $this->key = $key;
        return $this;
    }

    public function onDelete($action)
    {
        if (!in_array($action, $this->getAvailableActions())) {
            return $this;
        }
        $this->onDelete = $action;
        return $this;
    }

    public function onUpdate($action)
    {
        if (!in_array($action, $this->getAvailableActions())) {
            return $this;
        }
        $this->onUpdate = $action;
        return $this;
    }

    public function make($name)
    {
        return implode(' ', array_filter(array(
            'CONSTRAINT `' . $name . '`',
            'FOREIGN KEY (`' . $this->column . '`)',
            'REFERENCES `' . $this->table . '` (`' . $this->key . '`)',
            $this->makeOnDelete(),
            $this->makeOnUpdate()
        )));
    }

    protected function makeOnDelete()
    {
        if (empty($this->onDelete)) {
            return '';
        }
        return 'ON DELETE ' . $this->onDelete;
    }

    protected function makeOnUpdate()
    {
        if (empty($this->onUpdate)) {
            return '';
        }
        return 'ON UPDATE ' . $this->onUpdate;
    }

    public static function parse($str)
    {
        $matches = array();
        $actions = implode('|', static::getAvailableActions());
        if (preg_match('/CONSTRAINT\s+(.*)\s+FOREIGN KEY\s*\((.*)\)\s*REFERENCES\s+(.*)\s*\((.*)\)(?:\s*ON DELETE\s+(' . $actions . '))?(?:\s*ON UPDATE\s+(' . $actions . '))?/i', $str, $matches)) {
            print_r($matches);
            $fk = new ForeignKey(trim($matches[2], ' `'));
            $fk->references(trim($matches[3], ' `'), trim($matches[4], ' `'));
            if (!empty($matches[5])) {
                $fk->onDelete($matches[5]);
            }
            if (!empty($matches[6])) {
                $fk->onUpdate($matches[6]);
            }
            return $fk;
        }
        return null;
    }

    protected static function getAvailableActions()
    {
        return array(
            'CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION', 'SET DEFAULT'
        );
    }

}
