<?php

namespace Pina\DB;

class Field
{

    protected $name = '';
    protected $type = '';
    protected $length = '';
    protected $default = '';
    protected $null = '';
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

    public function make()
    {
        return '`' . $this->name . '`'
                . ' ' . $this->type
                . (!empty($this->length) ? '('.$this->length.')' : '')
                . $this->makeValues()
                . ' ' . (!empty($this->null) ? 'NULL' : 'NOT NULL')
                . (!empty($this->default) ? ' DEFAULT ' . $this->default : '')
                . (!empty($this->extra) ? ' ' . $this->extra: '');
    }
    
    protected function makeValues()
    {
        if (empty($this->values) || !is_array($this->values) || count($this->values) == 0) {
            return '';
        }
        return '('.implode(',', array_map(function($v) {
            return "'".$v."'";
        }, $this->values)).')';
    }

}
