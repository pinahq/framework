<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Html;
use Pina\Http\Location;

class LinkedListView extends Control
{

    /**
     * @var DataTable
     */
    protected $dataTable;

    /**
     * @param DataTable $dataTable
     */
    public function load($dataTable)
    {
        $this->dataTable = $dataTable;
        return $this;
    }

    protected function draw()
    {
        return Html::tag(
            'ul',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        $c = '';
        foreach ($this->dataTable as $record) {
            $c .= $this->makeItem($record);
        }
        return $c;
    }

    /**
     * @param DataRecord $record
     * @return Control
     */
    protected function makeItem($record)
    {
        $title = $record->getMeta('title');
        $link = $record->getMeta('link');
        if ($link) {
            /** @var LinkedListItem $item */
            $item = App::make(LinkedListItem::class);
            $item->setLink($link);
            $item->setText($title);
            return $this->addActiveClass($item, $record);
        }

        /** @var ListItem $item */
        $item = App::make(ListItem::class);
        $item->setText($title);
        return $this->addActiveClass($item, $record);
    }

    /**
     * @param ListItem $item
     * @param DataRecord $record
     * @return ListItem
     */
    protected function addActiveClass($item, $record)
    {
        if ($record->getMeta('is_active')) {
            $item->addClass('active');
        }
        return $item;
    }

}