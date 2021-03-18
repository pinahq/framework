<?php

namespace Pina\Http;

use Pina\App;
use Pina\Controls\RawHtml;
use Pina\Components\SortableListComponent;

abstract class SortableCollectionEndpoint extends CollectionEndpoint
{
    protected function makeIndexComponent()
    {
        return $this->makeSortableList()->setHandler(
            $this->base->resource('@/all/sortable'),
            'put',
            []
        );
    }

    /**
     * @return SortableListComponent
     */
    protected function makeSortableList()
    {
        return App::make(SortableListComponent::class);
    }

    protected function makeFilteredQuery($filters)
    {
        return parent::makeFilteredQuery($filters)->orderBy('order', 'asc');
    }

    protected function applyPaging($query, $filters)
    {
        return new RawHtml();
    }


}
