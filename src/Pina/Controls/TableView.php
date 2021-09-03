<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;

class TableView extends Card
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

    /**
     * @return \Pina\Data\Schema
     */
    public function getSchema()
    {
        return $this->dataTable->getSchema();
    }

    protected function drawInner()
    {
        $table = $this->makeTable();
        $table->append($this->buildHeader($this->dataTable->getSchema()->getTitles()));

        foreach ($this->dataTable as $record) {
            /** @var DataRecord $record */
            $table->append($this->makeRow()->load($record));
        }
        return $table;
    }

    protected function buildHeader($data)
    {
        $header = $this->makeTableRow();
        foreach ($data as $k => $v) {
            $header->append($this->makeTableHeaderCell()->setText($v));
        }
        return $header;
    }

    /**
     * @return RecordRow
     */
    protected function makeRow()
    {
        return App::make(RecordRow::class);
    }

    /**
     * @return Table
     */
    protected function makeTable()
    {
        return App::make(Table::class)->addClass('table table-hover');
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