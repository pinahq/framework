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
        $fields = $list->schema->getFields();
        $this->select = isset($fields[0]) ? $fields[0] : null;
        return parent::basedOn($list);
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
        return $r ? Html::tag('ul', $r) : '';
    }

}
