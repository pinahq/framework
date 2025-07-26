<?php

namespace Pina\Data;

use Iterator;
use Countable;
use Pina\Paging;

class DataTable implements Iterator, Countable
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
        foreach ($data as &$line) {
            $schema->fill($line);
        }
        $this->data = $data;
        $this->schema = $schema;

        if (is_null($paging)) {
            //в случае, если постраничная навигация не задана, создаем постраничную навигацию из одной большой страницы
            //но на странице не может быть 0 элементов, это вызовет деление на 0
            $this->paging = new Paging(0, max(1, count($this->data)));
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

    public function count(): int
    {
        return count($this->data);
    }

    /**
     *
     * @return \Pina\Data\DataRecord
     */
    public function current()
    {
        return new DataRecord($this->data[$this->cursor], $this->schema, true);
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