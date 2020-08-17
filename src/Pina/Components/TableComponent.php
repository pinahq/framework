<?php

namespace Pina\Components;

use Pina\Controls\TableHeaderCell;
use Pina\Controls\TableCell;
use Pina\Controls\TableRow;

class TableComponent extends ListData //implements ComponentInterface
{

    public function build()
    {
        $table = \Pina\Controls\Table::instance();
        $table->append($this->buildHeader($this->schema->getTitles()));
        foreach ($this as $record) {
            $table->append(RowComponent::instance()->basedOn($record));
        }
        $this->append($table);
    }

    protected function buildHeader($data)
    {
        $header = TableRow::instance();
        foreach ($data as $k => $v) {
            $header->append(TableHeaderCell::instance()->setText($v));
        }
        return $header;
    }

}
