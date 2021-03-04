<?php

namespace Pina\Components;

use Pina\Controls\TableCell;
use Pina\Controls\TableRow;

class RowComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $row = $this->makeTableRow();
        $data = $this->getHtmlData();
        if (!empty($data['class'])) {
            $row->addClass($data['class']);
        }
        $flat = $this->schema->makeFlatLine($data);
        foreach ($flat as $v) {
            $row->append($this->makeTableCell()->setText($v));
        }
        $this->append($row);
    }

    /**
     * @return TableRow
     */
    protected function makeTableRow()
    {
        return $this->control(TableRow::class);
    }

    /**
     * @return TableCell
     */
    protected function makeTableCell()
    {
        return $this->control(TableCell::class);
    }

}
