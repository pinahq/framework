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
     * @param array|null $data
     * @param Schema $schema
     * @throws \Exception
     */
    public function __construct($data, $schema)
    {
        //TODO: пока принимаем null, как возможный ответ из методов SQL first/find
        // нужно переделать вместе со строгими типами first/find
        if (is_null($data)) {
            $data = [];
        }
        $schema->fill($data);
        $this->data = $data;
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getData(): array
    {
        return $this->schema->processLineAsData($this->data);
    }

    public function getValue(string $key)
    {
        return $this->schema->processValueAsData($this->data, $key);
    }

    /**
     * @throws \Exception
     */
    public function getTextData(): array
    {
        return $this->schema->processLineAsText($this->data);
    }

    public function getTextValue(string $fieldName)
    {
        return $this->schema->processValueAsText($this->data, $fieldName);
    }

    /**
     * @throws \Exception
     */
    public function getHtmlData(): array
    {
        return $this->schema->processLineAsHtml($this->data);
    }

    public function getHtmlValue(string $fieldName)
    {
        return $this->schema->processValueAsHtml($this->data, $fieldName);
    }

    public function getInteractiveData(): array
    {
        return $this->schema->processLineAsInteractive($this->data);
    }

    public function getInteractiveValue($fieldName)
    {
        return $this->schema->processValueAsInteractive($this->data, $fieldName);
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

    public function getPrimaryKey($context = []): array
    {
        $pk = $this->schema->calculateContextPrimaryKey($context);
        $r = [];
        foreach ($pk as $name) {
            $r[$name] = $this->data[$name] ?? null;
        }
        return $r;
    }

    public function getSinglePrimaryKey($context = [])
    {
        $pk = $this->getPrimaryKey($context);
        if (empty($pk) || count($pk) > 1) {
            throw new \Exception("Primary key misconfiguration");
        }
        return array_shift($pk);
    }

}