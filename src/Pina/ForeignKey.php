<?php

namespace Pina;

class ForeignKey
{

    protected $columns = null;
    protected $table = null;
    protected $keys = null;
    protected $onDelete = '';
    protected $onUpdate = '';

    public function __construct($columns)
    {
        $this->columns = $columns;
    }

    public function references($table, $keys)
    {
        $this->table = $table;
        $this->keys = $keys;
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
            'FOREIGN KEY (' . $this->getColumns() . ')',
            'REFERENCES `' . $this->table . '` (' . $this->getKeys() . ')',
            $this->makeOnDelete(),
            $this->makeOnUpdate()
        )));
    }
    
    public function makeAdd($v)
    {
        return ' ADD ' . $this->make($v);
    }
    
    public function makeModify($v)
    {
        return ' DROP FOREIGN KEY `' . $v . '`, ADD ' . $this->make($v);
    }
    
    public function makeDrop($v)
    {
        return ' DROP FOREIGN KEY `' . $v . '`, ';
    }
    
    protected function getColumns()
    {
        if (is_array($this->columns)) {
            return implode(',', array_map(function($item) {
                return '`'.$item.'`';
            }, $this->columns));
        }
        return '`'.$this->columns.'`';
    }

    protected function getKeys()
    {
        if (is_array($this->keys)) {
            return implode(',', array_map(function($item) {
                return '`'.$item.'`';
            }, $this->keys));
        }
        return '`'.$this->keys.'`';
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

    protected static function getAvailableActions()
    {
        return array(
            'CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION', 'SET DEFAULT'
        );
    }

}
