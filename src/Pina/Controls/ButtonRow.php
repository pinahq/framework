<?php


namespace Pina\Controls;


use Pina\Html;

/**
 * Строчка из кнопок, поделена на две части: слева располагается основная кнопка, справа - дополнительные
 * @package Pina\Controls
 */
class ButtonRow extends Control
{
    /** @var Button */
    protected $main = null;

    /**
     * @param Button $button
     */
    public function setMain(Button $button)
    {
        $this->main = $button;
        return $this;
    }

    public function draw()
    {
        $compiled = $this->compile();
        if ($compiled) {
            $left = Html::tag('div', $this->main->draw(), ['class' => 'col-sm-4']);
            $right = Html::tag('div', $this->compile(), ['class' => 'col-sm-8 text-right']);
            return Html::tag('div', $left . $right, $this->makeAttributes(['class' => 'row']));
        }

        $attributes = $this->makeAttributes();
        if (count($attributes) > 0) {
            print_r($attributes);
            return Html::tag('div', $this->main->draw(), $this->makeAttributes());
        }

        return $this->main->draw();
    }
}