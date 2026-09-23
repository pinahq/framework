<?php

namespace Pina\Controls\Components;

use Pina\Controls\ControlContainer;
use Pina\Html;

/**
 * Карточка с наименованием и произвольным контентом
 * @package Pina\Controls
 */
class Card extends ControlContainer
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

    protected function drawContent(): string
    {
        return $this->drawHeader() . parent::drawContent();
    }

    protected function drawHeader()
    {
        return $this->title ? Html::tag('h5', $this->title, ['class' => 'card-title']) : '';
    }

    protected function draw(): string
    {
        return Html::tag('div', Html::tag('div', $this->drawContent(), ['class' => 'card-body']), $this->makeAttributes(['class' => 'card']));
    }

}
