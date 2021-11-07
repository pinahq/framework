<?php

namespace Pina\Data;

class DataTable implements \Iterator
{
    /** @var array */
    protected $data = [];

    /** @var Schema */
    protected $schema;

    protected $cursor = 0;

    /**
     * @param array $data
     * @param Schema $schema
     */
    public function __construct($data, $schema)
    {
        $this->data = $data;
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getData()
    {
        return $this->schema->processListAsData($this->data);
    }

    public function getTextData()
    {
        return $this->schema->processListAsText($this->data);
    }

    public function getHtmlData()
    {
        return $this->schema->processListAsHtml($this->data);
    }

    /**
     *
     * @return \Pina\Data\DataRecord
     */
    public function current()
    {
        return new DataRecord($this->data[$this->cursor], $this->schema);
    }

    public function key()
    {
        return $this->cursor;
    }

    public function next()
    {
        $this->cursor++;
    }

    public function rewind()
    {
        $this->cursor = 0;
    }

    public function valid()
    {
        return isset($this->data[$this->cursor]);
    }

}