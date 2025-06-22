<?php

namespace Pina\Http;

use Pina\App;
use Pina\Controls\RawHtml;
use Pina\Controls\SortableListView;
use Pina\Data\DataTable;

abstract class SortableCollectionEndpoint extends CollectionEndpoint
{
    protected function makeCollectionView(DataTable $data)
    {
        return $this->makeSortableList($data)->setHandler(
            $this->base()->resource('@/all/sortable'),
            'put',
            []
        );
    }

    /**
     * @return SortableListView
     */
    protected function makeSortableList(DataTable $data)
    {
        return App::make(SortableListView::class)->load($data);
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
