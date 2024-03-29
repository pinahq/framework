<?php


namespace Pina\Model;

use Countable;
use Iterator;

use function count;

class LinkedItemCollection implements Iterator, Countable
{
    protected $items = [];

    protected $cursor = 0;

    public function add(LinkedItemInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     *
     * @return LinkedItemInterface
     */
    public function current()
    {
        return $this->items[$this->cursor];
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
        return isset($this->items[$this->cursor]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
}