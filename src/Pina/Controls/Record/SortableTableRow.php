<?php

namespace Pina\Controls\Record;

use Pina\Html;

/**
 * Строчка таблицы
 */
class SortableTableRow extends RecordRow
{

    protected function draw(): string
    {
        $content = '';
        $content .= Html::zz('td(span.draggable [data-id=%](i.fa fa-arrows-alt-v))', $this->record->getMeta('id'));
        $data = $this->record->getHtmlData();
        foreach ($data as $v) {
            $content .= Html::tag('td', $v);
        }
        return Html::tag('tr', $content, $this->makeAttributes(['class' => $this->record->getMeta('class')]));
    }

}
