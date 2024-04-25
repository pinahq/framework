<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Html;

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
        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        if (empty($inner)) {
            return '';
        }

        return Html::tag(
            'ul',
            $inner,
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
            $item = $this->makeLinkedListItem();
            $item->setLink($link);
            $item->setText($title);
            return $this->addActiveClass($item, $record);
        }

        $item = $this->makeListItem();
        $item->setText($title);
        return $this->addActiveClass($item, $record);
    }

    protected function makeLinkedListItem(): LinkedListItem
    {
        return App::make(LinkedListItem::class);
    }

    protected function makeListItem(): ListItem
    {
        return App::make(ListItem::class);
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