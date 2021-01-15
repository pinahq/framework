<?php

namespace Pina\Components;

class RecordData extends Data
{

    protected $data = [];

    /**
     * 
     * @param \Pina\RecordData $record
     * @return $this
     */
    public function basedOn(RecordData $record)
    {
        return $this->load($record->data, $record->schema, $record->meta);
    }

    /**
     * 
     * @param type $data
     * @param \Pina\Components\Schema $schema
     * @return $this
     */
    public function load($data, Schema $schema, $meta = [])
    {
        $this->data = $schema->process($data);
        $this->schema = $schema;
        $this->meta = $meta;
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
    
    public function build()
    {
        $this->append(\Pina\Controls\Json::instance()->setData($this->data));
        return $this;
    }

}
