<?php

namespace Pina;

class Sorting
{
    private $field;
    private $direction;

    public function __construct($field, $direction)
    {
        $this->field = $field;
        if (in_array($direction, array('asc', 'desc'))) {
            $this->direction = $direction;
        } else {
            $this->direction = $direction?'asc':'desc';
        }

    }
    
    public function getField()
    {
        return $this->field;
    }
    
    public function getDirection()
    {
        return $this->direction;
    }

    public function setField($field)
    {
        $this->field = $field;
    }
    
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    public function fetch()
    {
        return array("field" => $this->field, "direction" => $this->direction);
    }

    public function load($data)
    {
        $this->field = $data["field"];
        $this->direction = $data["direction"];
    }

    public function isEmpty()
    {
        return empty($this->field);
    }
}
