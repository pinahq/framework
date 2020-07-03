<?php

namespace Pina\Components;

class ListData extends DataObject implements \Iterator
{

    protected $data = [];
    protected $cursor = 0;
    
    /**
     * 
     * @param \Pina\ListData $list
     * @return $this
     */
    public function basedOn(ListData $list)
    {
        return $this->load($list->data, $list->schema);
    }
    
    public function load($data, Schema $schema)
    {
        $this->data = $data;
        $this->schema = $schema;
        return $this;
    }
    
    public function add($line)
    {
        $this->data[] = $line;
    }
    
    public function forgetColumn($column)
    {
        $this->schema->forgetField($column);
        return $this;
    }
    
    /**
     * 
     * @return \Pina\RecordData
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
        $this->cursor ++;
    }

    public function rewind()
    {
        $this->cursor = 0;
    }

    public function valid()
    {
        return isset($this->data[$this->cursor]);
    }
    
    public function draw()
    {
        return \json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

}
