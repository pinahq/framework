<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\Field;
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
        $data = $this->record->getData();
        $this->record->getHtmlData();
        $content = '';
        foreach ($this->record->getSchema()->getIterator() as $field) {
            /** @var Field $field */
            if ($field->isHidden()) {
                continue;
            }
            $name = $field->getKey();
            $value = isset($data[$name]) ? $data[$name] : null;
            $type = $field->getType();
            $cell = App::type($type)->setContext($data)->format($value);
            $content .= Html::tag('td', $cell);
        }
        return $content;
    }

}