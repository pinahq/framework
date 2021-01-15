<?php

namespace Pina\Components;

class ListData extends Data implements \Iterator
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
        return $this->load($list->data, $list->schema, $list->meta);
    }
    
    public function load($data, Schema $schema, $meta = [])
    {
        $this->data = $schema->processList($data);
        $this->schema = $schema;
        $this->meta = $meta;
        return $this;
    }
    
    public function add($line)
    {
        $this->data[] = $line;
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
    
    public function build()
    {
        $this->append(\Pina\Controls\Json::instance()->setData($this->data));
        return $this;
    }

}
