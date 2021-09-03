<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Абзац
 * @package Pina\Controls
 */
class Paragraph extends Control
{

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

    protected function draw()
    {
        return Html::tag(
            'p',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->makeAttributes()
        );
    }

    protected function drawInner()
    {
        return $this->text;
    }

}
