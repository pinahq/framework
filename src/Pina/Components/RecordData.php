<?php

namespace Pina\Components;

use Pina\Controls\Json;

class RecordData extends Data
{

    private $data = [];

    /**
     *
     * @param RecordData $record
     * @return $this
     */
    public function basedOn(RecordData $record)
    {
        return $this->load($record->data, $record->schema, $record->meta);
    }

    /**
     *
     * @param array $data
     * @param \Pina\Components\Schema $schema
     * @return $this
     */
    public function load($data, Schema $schema, $meta = [])
    {
        $this->data = $data;
        $this->schema = $schema;
        $this->meta = $meta;
        return $this;
    }

    protected function getData()
    {
        return $this->schema->processLineAsData($this->data);
    }

    protected function getTextData()
    {
        return $this->schema->processLineAsText($this->data);
    }

    protected function getHtmlData()
    {
        return $this->schema->processLineAsHtml($this->data);
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
        $this->append(Json::instance()->setData($this->data));
        return $this;
    }

}
