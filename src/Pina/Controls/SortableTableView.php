<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\CSRF;

class SortableTableView extends SortableListView
{
    /**
     * @var DataTable
     */
    protected $dataTable;

    public function setHandler($resource, $method, $params)
    {
        $this->method = $method;
        $this->resource = ltrim($resource, '/');
        $this->params = htmlspecialchars(http_build_query($params), ENT_COMPAT);
        return $this;
    }

    /**
     * @param DataTable $dataTable
     */
    public function load($dataTable)
    {
        $this->dataTable = $dataTable;
        return $this;
    }

    /**
     * @return \Pina\Data\Schema
     */
    public function getSchema()
    {
        return $this->dataTable->getSchema();
    }

    protected function drawInner()
    {
        App::assets()->addScript('/static/default/js/pina.table-sortable.js');

        $table = $this->makeTable();
        $table->append($this->buildHeader($this->dataTable->getSchema()->getFieldTitles()));

        foreach ($this->dataTable as $record) {
            /** @var DataRecord $record */
            $table->append($this->makeRow()->load($record));
        }
        return $table;
    }

    protected function buildHeader($data)
    {
        $header = $this->makeTableRow();
        $header->append($this->makeTableHeaderCell()->setText(''));

        foreach ($data as $k => $v) {
            $header->append($this->makeTableHeaderCell()->setText($v));
        }
        return $header;
    }

    /**
     * @return SortableTableRow
     */
    protected function makeRow()
    {
        return App::make(SortableTableRow::class);
    }

    /**
     * @return Table
     */
    protected function makeTable()
    {
        return App::make(Table::class)->addClass('table table-hover pina-table-sortable')
            ->setDataAttribute('method', $this->method)
            ->setDataAttribute('csrf-token', CSRF::token())
            ->setDataAttribute('resource', $this->resource);
    }

    /**
     * @return TableRow
     */
    protected function makeTableRow()
    {
        return App::make(TableRow::class);
    }

    /**
     * @return TableHeaderCell
     */
    protected function makeTableHeaderCell()
    {
        return App::make(TableHeaderCell::class);
    }

}
