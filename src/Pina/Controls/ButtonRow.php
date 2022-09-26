<?php


namespace Pina\Controls;


use Pina\Html;

/**
 * Строчка из кнопок, поделена на две части: слева располагается основная кнопка, справа - дополнительные
 * @package Pina\Controls
 */
class ButtonRow extends Control
{
    /** @var Button|null */
    protected $main = null;

    /**
     * @param Button $button
     */
    public function setMain(Button $button)
    {
        $this->main = $button;
        return $this;
    }

    /**
     * @return Button
     */
    public function getMain()
    {
        return $this->main;
    }

    protected function draw()
    {
        $inner = $this->drawInnerBefore() . $this->drawInner(). $this->drawInnerAfter();
        if ($inner) {
            $left = Html::tag('div', $this->drawMain(), ['class' => 'col-sm-4']);
            $right = Html::tag('div', $inner, ['class' => 'col-sm-8 text-right']);
            return Html::tag('div', $left . $right, $this->makeAttributes(['class' => 'row']));
        }

        $attributes = $this->makeAttributes();
        if (count($attributes) > 0) {
            return Html::tag('div', $this->drawMain(), $this->makeAttributes());
        }

        return $this->drawMain();
    }

    protected function drawMain()
    {
        if (is_null($this->main)) {
            return '';
        }
        return $this->main->draw();
    }
}