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

    public function draw()
    {
        return Html::tag('p', $this->text . $this->compile(), $this->makeAttributes());
    }

}
