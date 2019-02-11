<?php

namespace Pina\DB;

class Index implements StructureItemInterface
{

    protected $columns = null;
    protected $type = '';

    public function __construct($columns)
    {
        $this->columns = $columns;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function make()
    {
        $v = '';
        return $this->type . ' KEY '
                . ($this->type != 'PRIMARY' && !empty($v) ? '`' . $v . '`' : '')
                . '(' . $this->getColumns() . ')';
    }

    public function makeAdd()
    {
        return 'ADD ' . $this->make();
    }

    public function makeModify($v)
    {
        return $this->makeDrop($v) . ',' . $this->makeAdd();
    }

    public function makeDrop($v)
    {
        if ($this->type == 'PRIMARY') {
            return 'DROP PRIMARY KEY';
        }
        return 'DROP KEY `' . $v . '`';
    }

    protected function getColumns()
    {
        if (is_array($this->columns)) {
            return implode(',', array_map(function($item) {
                        return '`' . $item . '`';
                    }, $this->columns));
        }
        return '`' . $this->columns . '`';
    }

}