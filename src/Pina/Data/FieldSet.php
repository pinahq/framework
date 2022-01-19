<?php

namespace Pina\Data;

use Pina\App;

class FieldSet
{
    /** @var Schema */
    protected $schema;
    /** @var Field[] */
    protected $fields = [];

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function add(Field $field)
    {
        $this->fields[] = $field;
    }

    public function calc($callable, $fieldKey, $fieldTitle, $fieldType = 'string')
    {
        $this->schema->pushDataProcessor(function ($item) use ($callable, $fieldKey) {
            $data = [];
            foreach ($this->fields as $f) {
                if (!isset($item[$f->getKey()])) {
                    continue;
                }
                $data[] = $item[$f->getKey()];
            }
            $item[$fieldKey] = $callable($data);
            return $item;
        });
        return $this->schema->add($fieldKey, $fieldTitle, $fieldType);
    }

    public function join($callable, $fieldKey, $fieldTitle, $fieldType = 'string')
    {
        foreach ($this->fields as $f) {
            $this->schema->forgetField($f->getKey());
        }
        return $this->calc($callable, $fieldKey, $fieldTitle, $fieldType);
    }

    public function printf($pattern, $fieldKey, $fieldTitle, $fieltType = 'string')
    {
        return $this->calc(function ($a) use ($pattern) {
            return vsprintf($pattern, $a);
        }, $fieldKey, $fieldTitle, $fieltType);
    }

    public function setNullable($nullable = true, $default = null)
    {
        foreach ($this->fields as $f) {
            $f->setNullable($nullable, $default);
        }
        return $this;
    }

    public function setMandatory($mandatory = true)
    {
        foreach ($this->fields as $f) {
            $f->setMandatory($mandatory);
        }
        return $this;
    }

    public function setStatic($static = true)
    {
        foreach ($this->fields as $f) {
            $f->setStatic($static);
        }
        return $this;
    }

    public function makeSchema()
    {
        $schema = new Schema();
        foreach ($this->fields as $f) {
            $schema->add(clone $f);
        }
        return $schema;
    }
}