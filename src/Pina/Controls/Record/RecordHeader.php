<?php

namespace Pina\Controls\Record;

use Pina\Controls\Control;
use Pina\Html;

class RecordHeader extends Control
{
    use RecordTrait;

    protected function draw(): string
    {
        $data = $this->record->getHtmlData();
        return Html::tag('h1', $data['title'], $this->makeAttributes());
    }

}