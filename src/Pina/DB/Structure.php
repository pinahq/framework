<?php

namespace Pina\DB;

class Structure
{

    protected $fields = array();
    protected $indexes = array();
    protected $constraints = array();

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }
    
    public function makeCreateTable($name, $extra = 'ENGINE=InnoDB DEFAULT CHARSET=utf8')
    {
        $schema = array_merge($this->getFields(), $this->getIndexes(), $this->getConstraints());
        $strings = array_map(array($this, 'callMake'), $schema);
        return 'CREATE TABLE IF NOT EXISTS `'.$name.'` ('."\n  ".implode(",\n  ", $strings)."\n".') '.$extra;
    }
    
    public function makeAlterTable($table, $existedStructure)
    {
        $conditions = $this->makePathTo($existedStructure);
        if (empty($conditions)) {
            return '';
        }
        return 'ALTER TABLE `'.$table.'` '.implode(', ', $conditions);
    }

    public function makePathTo($existedStructure)
    {
        $fields = $this->makeFieldPath($existedStructure->getFields(), $this->fields);
        $indexes = $this->makeIndexPath($existedStructure->getIndexes(), array_merge($this->indexes, $this->getConstraintIndexes()));
        $constraints = $this->makeIndexPath($existedStructure->getConstraints(), $this->constraints);
        return array_merge($fields, $indexes, $constraints);
    }
    
    protected function getConstraintIndexes()
    {
        $indexes = array();
        foreach ($this->constraints as $foreignKey) {
            $indexes[] = new Index($foreignKey->getColumns());
        }
        return $indexes;
    }

    public function makeFieldPath($from, $to)
    {
        $conditions = array();

        $gatewayStrings = array_reduce($to, array($this, 'reduceFields'), array());
        $existedStrings = array_reduce($from, array($this, 'reduceFields'), array());
        
        $toDelete = array_diff_key($existedStrings, $gatewayStrings);
        $toCreate = array_diff_key($gatewayStrings, $existedStrings);
        $toModify = array_intersect_key($gatewayStrings, $existedStrings);

        foreach ($toDelete as $name => $t) {
            $conditions[] = 'DROP COLUMN `' . $name . '`';
        }
        foreach ($toModify as $name => $cond) {
            if ($cond == $existedStrings[$name]) {
                continue;
            }
            $conditions[] = 'MODIFY ' . $cond;
        }
        foreach ($toCreate as $name => $cond) {
            $conditions[] = 'ADD COLUMN ' . $cond;
        }

        return $conditions;
    }

    public function makeIndexPath($from, $to)
    {
        $conditions = array();
        $gatewayStrings = array_map(array($this, 'callMake'), $to);
        $existedStrings = array_map(array($this, 'callMake'), $from);
        $toDelete = array_diff($existedStrings, $gatewayStrings);
        foreach ($toDelete as $indexKey => $t) {
            $conditions[] = $from[$indexKey]->makeDrop($indexKey);
        }
        $toCreate = array_diff($gatewayStrings, $existedStrings);
        foreach ($toCreate as $indexKey => $t) {
            $conditions[] = $to[$indexKey]->makeAdd();
        }

        return $conditions;
    }

    public function callMake(StructureItemInterface $key)
    {
        return $key->make();
    }

    public function reduceFields($carry, Field $field)
    {
        $carry[$field->getName()] = $field->make();
        return $carry;
    }

}