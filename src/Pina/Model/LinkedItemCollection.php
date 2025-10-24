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

    public function current(): LinkedItemInterface
    {
        return $this->items[$this->cursor];
    }

    public function key(): int
    {
        return $this->cursor;
    }

    public function next(): void
    {
        $this->cursor++;
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->cursor]);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}