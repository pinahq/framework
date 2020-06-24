<?php

namespace Pina\Components;

use Pina\Html;

class TableComponent extends ListData //implements ComponentInterface
{

    /**
     * 
     * @param \Pina\ListData $list
     * @return \Pina\TableComponent
     */
    public static function basedOn(ListData $list)
    {
        $r = new TableComponent();
        $r->load($list->data, $list->schema);
        return $r;
    }

    public function draw()
    {
        $r = $this->drawRow($this->schema->getTitles(), 'th');
        foreach ($this as $record) {
            $r .= RowComponent::basedOn($record)->draw();
        }
        return Html::tag('table', $r);
    }

    protected function drawRow($data, $tag = 'td')
    {
        $r = '';
        foreach ($data as $k => $v) {
            $r .= Html::tag($tag, $v);
        }
        return Html::tag('tr', $r);
    }

}
