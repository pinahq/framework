<?php

namespace Pina\Components;

use Pina\Controls\TableRow;
use Pina\Controls\TableCell;

class RowComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $data = $this->schema->makeFlatLine($this->data);
        $row = TableRow::instance();
        foreach ($data as $k => $v) {
            $row->append(TableCell::instance()->setText($v));
        }
        $this->append($row);
    }

}
