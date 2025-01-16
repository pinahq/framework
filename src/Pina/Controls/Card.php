<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Карточка с наименованием и произвольным контентом
 * @package Pina\Controls
 */
class Card extends Control
{

    protected $title = '';

    /**
     * Указать наименование карточки
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    protected function draw()
    {
        $header = '';
        if ($this->title) {
            $header = Html::tag('h5', $this->title, ['class' => 'card-title']);
        }
        $inner = $header . $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        if (empty($inner)) {
            return '';
        }
        return Html::tag(
            'div',
            Html::tag(
                'div',
                $inner,
                ['class' => 'card-body']
            ),
            $this->makeAttributes(['class' => 'card'])
        );
    }

}
