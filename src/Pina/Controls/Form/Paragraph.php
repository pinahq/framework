<?php

namespace Pina\Controls\Form;

use Pina\Controls\Control;
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

    protected function draw(): string
    {
        return Html::tag(
            'p',
            $this->drawContent(),
            $this->makeAttributes()
        );
    }

    protected function drawContent(): string
    {
        return $this->text;
    }

}
