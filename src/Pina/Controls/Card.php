<?php

namespace Pina\Controls;

use Pina\Html;

/**
 * Карточка с наименованием и произвольным контентом
 * @package Pina\Controls
 */
class Card extends Control
{

    use ContainerTrait;

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
        return Html::tag(
            'div',
            Html::tag(
                'div',
                $header . $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
                ['class' => 'card-body']
            ),
            $this->makeAttributes(['class' => 'card'])
        );
    }

    protected function drawInner()
    {
        return '';
    }

}
