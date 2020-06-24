<?php

namespace Pina\Components;

class RecordData extends DataObject
{

    protected $data = [];
    
    public function load($data, Schema $schema)
    {
        $this->data = $data;
        $this->schema = $schema;
        return $this;
    }
    
    public function get($field)
    {
        return $this->data[$field] ?? null;
    }

}
