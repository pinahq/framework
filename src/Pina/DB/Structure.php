<?php

namespace Pina\DB;

class Structure
{

    protected $constraints = null;

    public function __construct()
    {
        ;
    }

    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
    }

    public function makePathTo($existedConstraints)
    {
        $gatewayStrings = array_map(array($this, 'callMake'), $this->constraints);
        $existedStrings = array_map(array($this, 'callMake'), $existedConstraints);
        $conditions = array();
        $toDelete = array_diff($existedStrings, $gatewayStrings);
        foreach ($toDelete as $indexKey => $t) {
            $conditions[] = 'DROP FOREIGN KEY `' . $indexKey . '`'; //$existedConstraints[$indexKey]->makeDrop($indexKey);
        }
        $toCreate = array_diff($gatewayStrings, $existedStrings);
        foreach ($toCreate as $indexKey => $t) {
            $conditions[] = 'ADD ' . $t; //$this->constraints[$indexKey]->makeAdd();
        }
        return $conditions;
    }

    public function callMake($key)
    {
        return $key->make();
    }

}
