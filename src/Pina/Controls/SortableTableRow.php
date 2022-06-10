<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Строчка таблицы
 * @package Pina\Controls
 */
class SortableTableRow extends RecordRow
{

    public function draw()
    {
        $data = $this->record->getHtmlData();
        $content = '';
        $content .= Html::zz('td(span.draggable [data-id=%](i.fa fa-arrows-alt-v))', $this->record->getMeta('id'));
        foreach ($data as $v) {
            $content .= Html::tag('td', $v);
        }
        return Html::tag('tr', $content, $this->makeAttributes(['class' => $this->record->getMeta('class')]));
    }

}
