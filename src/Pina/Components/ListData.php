<?php

namespace Pina\Components;

use Iterator;
use Pina\Controls\Json;

class ListData extends Data implements Iterator
{

    private $data = [];
    protected $cursor = 0;

    /**
     *
     * @param \Pina\Components\ListData $list
     * @return $this
     */
    public function basedOn(ListData $list)
    {
        return $this->load($list->data, $list->schema, $list->meta);
    }

    public function load($data, Schema $schema, $meta = [])
    {
        $this->data = $data;
        $this->schema = $schema;
        $this->meta = $meta;
        return $this;
    }

    /**
     * Добавить строчку к данным
     * @param array $line
     * @return $this
     */
    public function push($line)
    {
        array_push($this->data, $line);
        return $this;
    }

    /**
     * Извлечь последнюю добавленную строчку из данных
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->data);
    }

    protected function getData()
    {
        return $this->schema->processListAsData($this->data);
    }

    protected function getTextData()
    {
        return $this->schema->processListAsText($this->data);
    }

    protected function getHtmlData()
    {
        return $this->schema->processListAsHtml($this->data);
    }

    /**
     *
     * @return \Pina\Components\RecordData
     */
    public function current()
    {
        return (new RecordData())->load($this->data[$this->cursor], $this->schema);
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

    public function build()
    {
        $json = new Json();
        $this->append($json->setData($this->data));
        return $this;
    }

}
