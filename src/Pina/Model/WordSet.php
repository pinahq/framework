<?php


namespace Pina\Model;


class WordSet
{

    protected $list = [];

    public function add(string $item)
    {
        $this->list = array_unique(array_merge($this->list, $this->makeWords($item)));
    }

    public function remove(string $item)
    {
        $this->list = array_diff($this->list, $this->makeWords($item));
    }

    public function has(string $item): bool
    {
        $needle = $this->makeWords($item);
        return count(array_intersect($this->list, $needle)) == count($needle);
    }

    public function clear()
    {
        $this->list = [];
    }

    public function __toString()
    {
        return implode(' ', $this->list);
    }

    protected function makeWords(string $item): array
    {
        return array_filter(preg_split('/\s+/si', $item));
    }

}