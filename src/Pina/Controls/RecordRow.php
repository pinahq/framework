<?php

namespace Pina\Controls;

use Pina\Html;

class RecordRow extends Control
{
    use RecordTrait;

    protected function draw()
    {
        return Html::tag(
            'tr',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes(['class' => $this->record->getMeta('class')])
        );
    }

    protected function drawInner()
    {
        $data = $this->record->getHtmlData();
        $content = '';
        foreach ($data as $v) {
            $content .= Html::tag('td', $v);
        }
        return $content;
    }

}