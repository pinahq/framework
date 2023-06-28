<?php

namespace Pina\Controls;

use Pina\App;
use Pina\CSRF;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Html;

/**
 * Class SortableListView
 * @package Pina\Controls
 * TODO: унаследовать все-таки от UnorderedList, а не от карточки. Для этого переделать логику работы after/before, они должны вставлять контент после враппера
 */
class SortableListView extends Card
{

    protected $method = '';
    protected $resource = '';
    protected $params = '';

    /**
     * @var DataTable
     */
    protected $dataTable;

    /**
     * @param DataTable $dataTable
     */
    public function load($data)
    {
        $this->dataTable = $data;
        return $this;
    }

    public function setHandler($resource, $method, $params)
    {
        $this->method = $method;
        $this->resource = ltrim($resource, '/');
        $this->params = htmlspecialchars(http_build_query($params), ENT_COMPAT);
        return $this;
    }

    protected function drawInner()
    {
        App::assets()->addScript('/static/default/js/pina.sortable.js');

        $list = $this->makeList();
        $list->setDataAttribute('method', $this->method);
        $list->setDataAttribute('resource', $this->resource);
        $list->setDataAttribute('params', $this->params);
        $csrfAttributes = CSRF::tagAttributeArray($this->method);
        if (!empty($csrfAttributes['data-csrf-token'])) {
            $list->setDataAttribute('csrf-token', $csrfAttributes['data-csrf-token']);
        }
        foreach ($this->dataTable as $record) {
            /** @var DataRecord $record */
            $list->append($this->makeListItem($record));
        }
        return $list;
    }

    /**
     * @return UnorderedList
     */
    protected function makeList()
    {
        return App::make(UnorderedList::class)
            ->addClass('feeds')
            ->addClass('ui-sortable')
            ->addClass('pina-sortable');
    }

    /**
     * @param DataRecord $record
     * @return ListItem
     */
    protected function makeListItem($record)
    {
        /** @var ListItem $li */
        $li = App::make(ListItem::class);
        $li->addClass('draggable')->addClass('ui-sortable-handle');
        $li->addClass($record->getMeta('class'));
        $li->setDataAttribute('id', $record->getMeta('id'));

        $subtitle = $record->getMeta('subtitle');
        $muted = '';
        if (!empty($subtitle)) {
            $muted = Html::tag('span', $subtitle, ['class' => 'text-muted']);
        }
        $li->setText($record->getMeta('icon') . $muted . Html::a($record->getMeta('title'), $record->getMeta('link')));
        return $li;
    }

}