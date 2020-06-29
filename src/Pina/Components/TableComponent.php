<?php

namespace Pina\Components;

use Pina\Html;

class TableComponent extends ListData //implements ComponentInterface
{

    /**
     * 
     * @param \Pina\ListData $list
     * @return $this
     */
    public function basedOn(ListData $list)
    {
        return $this->load($list->data, $list->schema);
    }

    public function draw()
    {
        $r = $this->drawHeader($this->schema->getTitles());
        foreach ($this as $record) {
            $r .= RowComponent::instance()->basedOn($record)->draw();
        }
        return Html::tag('table', $r);
    }

    protected function drawHeader($data)
    {
        $r = '';
        foreach ($data as $k => $v) {
            $r .= Html::tag('th', $v);
        }
        return Html::tag('tr', $r);
    }

}
