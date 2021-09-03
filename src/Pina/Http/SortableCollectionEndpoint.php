<?php

namespace Pina\Http;

use Pina\App;
use Pina\Controls\RawHtml;
use Pina\Controls\SortableListView;

abstract class SortableCollectionEndpoint extends CollectionEndpoint
{
    protected function makeCollectionView()
    {
        return $this->makeSortableList()->setHandler(
            $this->base->resource('@/all/sortable'),
            'put',
            []
        );
    }

    /**
     * @return SortableListView
     */
    protected function makeSortableList()
    {
        return App::make(SortableListView::class);
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
