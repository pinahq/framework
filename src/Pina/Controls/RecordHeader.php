<?php

namespace Pina\Controls;

use Pina\Html;

class RecordHeader extends Control
{
    use RecordTrait;

    protected function draw()
    {
        $data = $this->record->getHtmlData();
        return Html::tag('h1', $data['title'], $this->makeAttributes());
    }

}