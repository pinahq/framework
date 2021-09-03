<?php

namespace Pina\Components;

use Pina\Controls\Card;

/**
 * @deprecated see \Pina\Controls\TableView
 */
class TableComponent extends SimpleTableComponent //implements ComponentInterface
{

    public function build()
    {
        $table = $this->buildTable();
        $container = new Card;
        $container->append($table);
        $this->append($container);
    }
    
    /**
     * @return \Pina\Controls\Table
     */
    protected function makeTable()
    {
        $table = $this->control(\Pina\Controls\Table::class);
        $table->addClass('table');
        $table->addClass('table-hover');
        
        return $table;
    }

}
