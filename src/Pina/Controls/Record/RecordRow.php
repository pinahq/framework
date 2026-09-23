<?php

namespace Pina\Controls\Record;

use Pina\Controls\Control;
use Pina\Html;

class RecordRow extends Control
{
    use RecordTrait;

    /**
     * @return string
     * @throws \Exception
     */
    protected function draw(): string
    {
        return Html::tag('tr', $this->drawContent(), $this->makeAttributes(['class' => $this->record->getMeta('class')]));
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function drawContent(): string
    {
        $content = '';
        $data = $this->record->getHtmlData();
        foreach ($data as $v) {
            $content .= Html::tag('td', $v);
        }
        return $content;
    }

}