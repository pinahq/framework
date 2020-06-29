<?php

namespace Pina\Components;

class RecordData extends DataObject
{

    protected $data = [];
    
    /**
     * 
     * @param type $data
     * @param \Pina\Components\Schema $schema
     * @return $this
     */
    public function load($data, Schema $schema)
    {
        $this->data = $data;
        $this->schema = $schema;
        return $this;
    }
    
    /**
     * Получить поле записи
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        return isset($this->data[$field]) ? $this->data[$field] : null;
    }

}
