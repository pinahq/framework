<?php

namespace Pina\Components;

use Pina\Html;

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
        $this->load($list->data, $list->schema);
        $fields = $list->schema->getFields();
        $this->select = isset($fields[0]) ? $fields[0] : null;
        return $this;
    }

    public function select($column)
    {
        $this->select = $column;
        return $this;
    }

    public function draw()
    {
        $r = '';
        foreach ($this as $row) {
            $r .= Html::tag('li', $row->get($this->select));
        }
        return Html::tag('ul', $r);
    }

}
