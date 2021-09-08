<?php

namespace Pina\Data;

class DataRecord
{

    protected $data = [];

    protected $meta = null;

    /**
     * @var Schema
     */
    protected $schema;

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
        return $this->schema->processLineAsData($this->data);
    }

    public function getTextData()
    {
        return $this->schema->processLineAsText($this->data);
    }

    public function getHtmlData()
    {
        return $this->schema->processLineAsHtml($this->data);
    }

    public function getMetaData()
    {
        if (!is_null($this->meta)) {
            return $this->meta;
        }
        return $this->meta = $this->schema->processLineAsMeta($this->data);
    }


    public function getMeta($prop)
    {
        $meta = $this->getMetaData();
        return isset($meta[$prop]) ? $meta[$prop] : (isset($this->data[$prop]) ? $this->data[$prop] : null);
    }

}