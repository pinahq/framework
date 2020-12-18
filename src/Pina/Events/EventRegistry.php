<?php

namespace Pina\Events;

class EventRegistry implements \Iterator
{
    protected $cursor = 0;
    protected $data = [];

    public function push($event)
    {
        array_push($this->data, $event);
    }
    
    /**
     * 
     * @return \Pina\Events\EventHandlerInterface
     */
    public function current()
    {
        return $this->data[$this->cursor];
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
    
}