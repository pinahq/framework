<?php

namespace Pina;

class Paging
{

    public $current;
    public $perPage;
    public $total;

    public function __construct($current, $perPage)
    {
        $this->current = $current;
        $this->perPage = $perPage;
    }

    public static function create($current, $perPage)
    {
        $p = new Paging($current, $perPage);
        return $p;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getStart()
    {
        if ($this->current < 1) {
            return 0;
        }

        $pagesCount = $this->getPagesCount();

        if ($pagesCount < 1) {
            $pagesCount = 1;
        }

        if (!empty($pagesCount) && $this->current > $pagesCount) {
            $this->current = $pagesCount;
        }

        return ($this->current - 1) * $this->perPage;
    }

    public function getCurrent()
    {
        if ($this->current == 0) {
            return 1;
        }

        return $this->current;
    }

    public function getCount()
    {
        return $this->perPage;
    }

    public function getPagesCount()
    {
        return ceil($this->total / $this->perPage);
    }

    public function slice($ids)
    {
        return array_slice($ids, $this->getStart(), $this->getCount());
    }

    public function fetch()
    {
        return [
            'paging' => $this->perPage,
            'start' => $this->getStart() + 1,
            'end' => min($this->getStart() + $this->perPage, $this->total),
            'current' => $this->getCurrent(),
            'total' => $this->getPagesCount(),
            'items' => $this->total,
            'resource' => App::resource(),
        ];
    }

}
