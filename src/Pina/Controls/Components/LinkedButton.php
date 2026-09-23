<?php

namespace Pina\Controls\Components;

use Pina\Html;

/**
 * Копка в виде ссылки
 * @package Pina\Controls
 */
class LinkedButton extends Button
{

    protected $link = '';

    /**
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    protected function draw(): string
    {
        return Html::a(
            $this->drawContent(),
            $this->link,
            $this->makeAttributes(['class' => 'btn btn-' . $this->style])
        );
    }

}
