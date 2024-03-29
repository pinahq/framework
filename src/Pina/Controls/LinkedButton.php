<?php

namespace Pina\Controls;

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

    protected function draw()
    {
        return Html::a(
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            $this->link,
            $this->makeAttributes(['class' => 'btn btn-' . $this->style])
        );
    }

}
