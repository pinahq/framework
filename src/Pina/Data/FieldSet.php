<?php

namespace Pina\Data;

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

    public function count()
    {
        return count($this->fields);
    }

    public function add(Field $field)
    {
        $this->fields[] = $field;
    }

    public function select(string $fieldKey)
    {
        foreach ($this->schema->getIterator() as $field) {
            if ($field->getName() == $fieldKey) {
                $this->fields[] = $field;
            }
        }
    }

    public function calc($callable, $fieldKey, $fieldTitle, $fieldType = 'string')
    {
        $this->schema->pushDataProcessor(function ($item) use ($callable, $fieldKey) {
            $data = [];
            foreach ($this->fields as $f) {
                if (!isset($item[$f->getName()])) {
                    continue;
                }
                $data[] = $item[$f->getName()];
            }
            $item[$fieldKey] = $callable($data);
            return $item;
        });
        return $this->schema->add($fieldKey, $fieldTitle, $fieldType);
    }

    public function join($callable, $fieldKey, $fieldTitle, $fieldType = 'string')
    {
        foreach ($this->fields as $f) {
            $this->schema->forgetField($f->getName());
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

    public function setImmutable($static = true)
    {
        foreach ($this->fields as $f) {
            $f->setImmutable($static);
        }
        return $this;
    }

    public function setHidden($hidden = true)
    {
        foreach ($this->fields as $f) {
            $f->setHidden($hidden);
        }
        return $this;
    }

    public function setMultiple($multiple = true) {
        foreach ($this->fields as $f) {
            $f->setMultiple($multiple);
        }
        return $this;
    }

    public function setAlias($key, $alias) {
        foreach ($this->fields as $f) {
            if ($f->getName() == $key) {
                $f->setAlias($alias);
            }
        }
        return $this;
    }

    public function setTitle($key, $title) {
        foreach ($this->fields as $f) {
            if ($f->getName() == $key) {
                $f->setTitle($title);
            }
        }
        return $this;
    }

    public function tag($tag)
    {
        foreach ($this->fields as $f) {
            $f->tag($tag);
        }
        return $this;
    }

    public function makeSchema()
    {
        $schema = new Schema();
        $keys = [];
        foreach ($this->fields as $f) {
            $keys[] = $f->getName();
            $schema->add(clone $f);
        }

        $pk = $this->schema->getPrimaryKey();
        if ($pk && count(array_intersect($pk, $keys)) == count($pk)) {
            $schema->setPrimaryKey($pk);
        }

        $uks = $this->schema->getUniqueKeys();
        foreach ($uks as $uk) {
            if ($uk && count(array_intersect($uk, $keys)) == count($uk)) {
                $schema->addUniqueKey($uk);
            }
        }

        //TODO: добавить перенос field definitions

        return $schema;
    }
}