<?php

namespace Pina\Controls;

/**
 * Произвольный HTML
 * @package Pina\Controls
 */
class RawHtml extends Control
{

    protected $text = '';

    public function __construct(string $text = '')
    {
        $this->text = $text;
    }

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
        return $this->text;
    }

}
