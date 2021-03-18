<?php

namespace Pina\Components;

use Pina\App;
use Pina\Controls\Table;
use Pina\Controls\TableHeaderCell;
use Pina\Controls\TableRow;

class SimpleTableComponent extends ListData //implements ComponentInterface
{

    public function build()
    {
        $this->append($this->buildTable());
    }

    protected function buildTable()
    {
        $table = $this->makeTable();
        $table->append($this->buildHeader($this->schema->getTitles()));
        foreach ($this as $record) {
            $table->append($this->makeRow()->basedOn($record));
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
     * @return RowComponent
     */
    protected function makeRow()
    {
        return App::make(RowComponent::class);
    }

    /**
     * @return Table
     */
    protected function makeTable()
    {
        return $this->control(Table::class);
    }

    /**
     * @return TableRow
     */
    protected function makeTableRow()
    {
        return $this->control(TableRow::class);
    }

    /**
     * @return TableHeaderCell
     */
    protected function makeTableHeaderCell()
    {
        return $this->control(TableHeaderCell::class);
    }

}
