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

    public function makePathTo($existedStructure)
    {
        $indexes = $this->makeIndexPath($existedStructure->getIndexes(), $this->indexes);
        $constraints = $this->makeIndexPath($existedStructure->getConstraints(), $this->constraints);
        return array_merge($indexes, $constraints);
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

    public function callMake($key)
    {
        return $key->make();
    }

}
