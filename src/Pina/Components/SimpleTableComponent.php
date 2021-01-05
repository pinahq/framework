<?php

namespace Pina\Components;

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
            $table->append(RowComponent::instance()->basedOn($record));
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
     * @return \Pina\Controls\Table
     */
    protected function makeTable()
    {
        return $this->control(\Pina\Controls\Table::class);
    }

    /**
     * @return \Pina\Controls\TableRow
     */
    protected function makeTableRow()
    {
        return $this->control(\Pina\Controls\TableRow::class);
    }
    
    /**
     * @return \Pina\Controls\TableHeaderCell
     */
    protected function makeTableHeaderCell()
    {
        return $this->control(\Pina\Controls\TableHeaderCell::class);
    }

}
