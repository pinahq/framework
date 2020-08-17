<?php

namespace Pina\Components;

use Pina\Controls\UnorderedList;
use Pina\Controls\ListItem;

class ListComponent extends ListData //implements ComponentInterface
{

    protected $select = null;

    /**
     * 
     * @param \Pina\ListData $list
     * @return $this
     */
    public function basedOn(ListData $list)
    {
        $fields = $list->schema->getFields();
        $this->select = isset($fields[0]) ? $fields[0] : null;
        return parent::basedOn($list);
    }

    public function select($column)
    {
        $this->select = $column;
        return $this;
    }

    public function build()
    {
        $list = UnorderedList::instance();
        foreach ($this as $row) {
            $text = $row->get($this->select);
            $list->append(ListItem::instance()->setText($text));
        }
        $this->append($list);
    }

}
