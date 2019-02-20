<?php

namespace Pina\DB;

class Field implements StructureItemInterface
{

    protected $name = '';
    protected $type = '';
    protected $length = '';
    protected $unsigned = false;
    protected $zerofill = false;
    protected $default = null;
    protected $null = true;
    protected $values = '';
    protected $extra = '';

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function length($length)
    {
        $this->length = $length;
        return $this;
    }
    
    public function unsigned()
    {
        $this->unsigned = true;
    }
    
    public function zerofill()
    {
        $this->zerofill = true;
    }

    public function def($def)
    {
        $this->default = $def;
        return $this;
    }

    public function isNull($null)
    {
        $this->null = $null;
        return $this;
    }

    public function values($values)
    {
        $this->values = $values;
        return $this;
    }

    public function extra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function make()
    {
        return implode(' ', array_filter(array(
            '`' . $this->name . '`',
            strtoupper($this->type) . (!empty($this->length) ? '(' . $this->length . ')' : '') . $this->makeValues(),
            $this->unsigned ? 'UNSIGNED' : '',
            $this->zerofill ? 'ZEROFILL' : '',
            (!empty($this->null) ? 'NULL' : 'NOT NULL'),
            $this->makeDefault(),
            $this->extra,
        )));
    }

    protected function makeValues()
    {
        if (empty($this->values) || !is_array($this->values) || count($this->values) == 0) {
            return '';
        }
        return '(' . implode(',', array_map(function($v) {
                            return "'" . $v . "'";
                        }, $this->values)) . ')';
    }

    protected function makeDefault()
    {
        if (!isset($this->default)) {
            return;
        }

        if (in_array($this->default, array('NULL', 'CURRENT_TIMESTAMP', 'NOW()'))) {
            return 'DEFAULT ' . $this->default;
        }

        return "DEFAULT '" . $this->default . "'";
    }

}
