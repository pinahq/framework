<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Components\Card;
use Pina\Controls\Components\Table;
use Pina\Controls\Components\TableHeaderCell;
use Pina\Controls\Components\TableRow;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Field;
use Pina\Http\Location;

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
        $this->location = App::location('@');
    }

    public function setLocation(Location $location, array $context = [])
    {
        $this->location = $location;
        $this->context = $context;
        return $this;
    }

    public static function make(?DataTable $dataTable = null)
    {
        $inst = parent::make();
        $inst->dataTable = $dataTable;
        return $inst;
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

    protected function drawContent(): string
    {
        $table = $this->makeTable();
        $table->append($this->buildHeader());

        foreach ($this->dataTable as $record) {
            /** @var DataRecord $record */
            $table->append($this->makeRow($record));
        }
        return $table . parent::drawContent();
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