<?php

namespace Pina\Controls;

use Pina\Html;

class RecordRow extends Control
{
    use RecordTrait;

    /**
     * @return string
     * @throws \Exception
     */
    protected function draw()
    {
        return Html::tag(
            'tr',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes(['class' => $this->record->getMeta('class')])
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function drawInner()
    {
        $content = '';
        $data = $this->record->getHtmlData();
        foreach ($data as $v) {
            $content .= Html::tag('td', $v);
        }
        return $content;
    }

}