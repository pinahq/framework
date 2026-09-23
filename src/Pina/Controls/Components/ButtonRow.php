<?php


namespace Pina\Controls\Components;


use Pina\Controls\ControlContainer;
use Pina\Html;

/**
 * Строчка из кнопок, поделена на две части: слева располагается основная кнопка, справа - дополнительные
 * @package Pina\Controls
 */
class ButtonRow extends ControlContainer
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

    protected function draw(): string
    {
        $inner = $this->drawContent();
        if ($inner) {
            $left = Html::tag('div', $this->drawMain(), ['class' => 'col-sm-4']);
            $right = Html::tag('div', $inner, ['class' => 'col-sm-8 text-right']);
            return Html::tag('div', $left . $right, $this->makeAttributes(['class' => 'buttons row']));
        }

        return Html::tag('div', $this->drawMain(), $this->makeAttributes(['class' => 'buttons']));
    }

    protected function drawMain()
    {
        if (is_null($this->main)) {
            return '';
        }
        return $this->main->draw();
    }
}