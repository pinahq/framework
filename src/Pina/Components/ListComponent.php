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
        $list = $this->makeUnorderedList();
        foreach ($this as $row) {
            $text = $row->get($this->select);
            $list->append($this->makeListItem()->setText($text));
        }
        $this->append($list);
    }
    
    /**
     * @return \Pina\Controls\UnorderedList
     */
    protected function makeUnorderedList()
    {
        return $this->control(\Pina\Controls\UnorderedList::class);
    }

    /**
     * @return \Pina\Controls\ListItem
     */
    protected function makeListItem()
    {
        return $this->control(\Pina\Controls\ListItem::class);
    }

}
