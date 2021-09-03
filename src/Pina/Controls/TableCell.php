<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Ячейка таблицы
 * @package Pina\Controls
 */
class TableCell extends Control
{
    use ContainerTrait;

    protected $text = '';

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function draw()
    {
        return Html::tag(
            'td',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        return $this->text;
    }

}
