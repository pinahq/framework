<?php

namespace Pina\Data;

use Iterator;
use Pina\Paging;

class DataTable implements Iterator
{
    /** @var array */
    protected $data = [];

    /** @var Schema */
    protected $schema;

    protected $cursor = 0;

    protected $paging;

    /**
     * @param array $data
     * @param Schema $schema
     */
    public function __construct($data, $schema, ?Paging $paging = null)
    {
        $this->data = $data;
        $this->schema = $schema;

        if (is_null($paging)) {
            $this->paging = new Paging(0, count($this->data));
            $this->paging->setTotal(count($this->data));
        } else {
            $this->paging = $paging;
        }
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function getPaging(): Paging
    {
        return $this->paging;
    }

    public function getData()
    {
        return $this->schema->processListAsData($this->data);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTextData()
    {
        return $this->schema->processListAsText($this->data);
    }

    /**
     * @return array
     * @throws \Exception
     */
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