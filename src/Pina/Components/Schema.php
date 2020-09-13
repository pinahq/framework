<?php

namespace Pina\Components;

class Schema
{

    protected $fields = [];
    protected $titles = [];
    protected $types = []; // string, integer, float, date

    public function add($field, $title, $type = '')
    {
        $this->fields[] = $field;
        $this->titles[] = $title;
        $this->types[] = $type;
    }

    public function forgetField($key)
    {
        $index = array_search($key, $this->fields);
        if ($index === false) {
            return $this;
        }

        array_splice($this->fields, $index, 1);
        array_splice($this->titles, $index, 1);
        array_splice($this->types, $index, 1);

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getTitles()
    {
        return $this->titles;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getTitle($field)
    {
        $index = array_search($field, $this->fields);
        return $this->titles[$index];
    }

    public function getType($field)
    {
        $index = array_search($field, $this->fields);
        return $this->types[$index];
    }

    public function fetch()
    {
        $data = [];
        foreach ($this->fields as $k => $v) {
            $data[] = [$v, $this->titles[$k]];
        }
        return $data;
    }

    public function makeFlatLine($line)
    {
        $newLine = [];
        foreach ($this->fields as $k) {
            $newLine[] = $line[$k] ? $line[$k] : '';
        }
        return $newLine;
    }

    public function makeFlatTable(&$table)
    {
        $flat = [];
        foreach ($table as $v) {
            $flat[] = $this->makeFlatLine($v);
        }
        return $flat;
    }

}
