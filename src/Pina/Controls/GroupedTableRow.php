<?php


namespace Pina\Controls;

use Pina\Html;

class GroupedTableRow extends Control
{

    protected $title = '';
    protected $count = 0;

    public function load(string $title, int $count)
    {
        $this->title = $title;
        $this->count = $count;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function draw()
    {
        if (empty($this->count)) {
            return '';
        }
        return Html::zz('tr(th[colspan=%]%', $this->count, $this->title);
    }
}