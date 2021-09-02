<?php

namespace Pina\Controls;

use Pina\Data\DataRecord;
use Pina\Html;

class RecordRow extends Control
{
    use RecordTrait;

    protected function draw()
    {
        $data = $this->record->getHtmlData();
        $content = '';
        foreach ($data as $v) {
            $content .= Html::tag('td', $v);
        }
        return Html::tag('tr', $content, $this->makeAttributes(['class' => $this->record->getMeta('class')]));
    }

}