<?php

namespace Pina\Controls;

use Pina\Data\DataRecord;

class BreadcrumbView extends LinkedListView
{

    public function __construct()
    {
        $this->addClass('breadcrumb');
    }

    /**
     * @param DataRecord $record
     * @return Control
     */
    protected function makeItem($record)
    {
        $item = parent::makeItem($record);
        $item->addClass('breadcrumb-item');
        return $item;
    }

}