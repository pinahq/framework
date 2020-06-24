<?php

namespace Pina\Components;

use Pina\Html;

class ListComponent extends ListData //implements ComponentInterface
{

    protected $select = null;

    /**
     * 
     * @param \Pina\ListData $list
     * @return \Pina\TableComponent
     */
    public static function basedOn(ListData $list)
    {
        $r = new ListComponent();
        $r->load($list->data, $list->schema);
        $fields = $list->schema->getFields();
        $r->select = $fields[0] ?? null;
        return $r;
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
