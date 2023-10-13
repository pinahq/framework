<?php

namespace Pina\Controls;

use Pina\Html;

class SidebarWrapper extends Control
{

    /**
     * @var Control[]
     */
    protected $sidebar = [];
    protected $width = 8;

    public function setSidebar(Control $sidebar)
    {
        $this->sidebar = [];
        return $this->addToSidebar($sidebar);
    }

    public function addToSidebar(Control $control)
    {
        $this->sidebar[] = $control;
        return $this;
    }

    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    protected function draw()
    {
        $left = Html::tag(
            'div',
            $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter(),
            ['class' => 'col-lg-' . $this->width]
        );

        return Html::tag('div', $left . $this->drawSidebar(), $this->makeAttributes(['class' => 'row']));
    }

    protected function drawSidebar()
    {
        if (empty($this->sidebar)) {
            return '';
        }
        return Html::tag('div', implode('', $this->sidebar), ['class' => 'col-lg-' . (12 - $this->width)]);
    }

}
