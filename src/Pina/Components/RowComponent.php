<?php

namespace Pina\Components;

use Pina\Controls\TableRow;
use Pina\Controls\TableCell;

class RowComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $data = $this->schema->makeFlatLine($this->data);
        $row = $this->makeTableRow();
        foreach ($data as $k => $v) {
            $row->append($this->makeTableCell()->setText($v));
        }
        $this->append($row);
    }

    /**
     * @return \Pina\Controls\TableRow
     */
    protected function makeTableRow()
    {
        return $this->control(\Pina\Controls\TableRow::class);
    }
    
    /**
     * @return \Pina\Controls\TableCell
     */
    protected function makeTableCell()
    {
        return $this->control(\Pina\Controls\TableCell::class);
    }

}
