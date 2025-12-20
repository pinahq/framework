<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Field;
use Pina\Http\Location;
use Pina\Input;

class TableView extends Card
{
    /**
     * @var DataTable
     */
    protected $dataTable;

    /** @var Location */
    protected $location;

    protected $context = [];

    public function __construct()
    {
        //клонировать не нужно, так как неизменяемый объект
        $this->location = App::baseUrl()->location(Input::getResource());
    }

    public function setLocation(Location $location, array $context = [])
    {
        $this->location = $location;
        $this->context = $context;
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
        $table = $this->makeTable();
        $table->append($this->buildHeader());

        foreach ($this->dataTable as $record) {
            /** @var DataRecord $record */
            $table->append($this->makeRow($record));
        }
        return $table;
    }

    protected function buildHeader()
    {
        $header = $this->makeTableRow();

        foreach ($this->dataTable->getSchema()->getIterator() as $field) {
            /** @var Field $field */
            if ($field->isHidden()) {
                continue;
            }
            $header->append($this->makeTableHeaderCell($field)->setText($field->getTitle()));
        }
        return $header;
    }

    /**
     * @return RecordRow
     */
    protected function makeRow(DataRecord $record)
    {
        return App::make(RecordRow::class)->load($record);
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
    protected function makeTableHeaderCell(Field $field)
    {
        return App::make(TableHeaderCell::class);
    }


}