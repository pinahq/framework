<?php

namespace Pina\Controls;

use Pina\Html;

class SidebarWrapper extends Control
{
    use ContainerTrait;

    /**
     * @var Control
     */
    protected $sidebar;
    protected $width = 8;

    public function setSidebar(Control $sidebar)
    {
        $this->sidebar = $sidebar;
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

        return Html::tag('div', $left . $this->drawSidebar(), ['class' => 'row']);
    }

    protected function drawInner()
    {
        return '';
    }

    protected function drawSidebar()
    {
        if (empty($this->sidebar)) {
            return '';
        }
        return Html::tag('div', $this->sidebar->drawWithWrappers(), ['class' => 'col-lg-' . (12 - $this->width)]);
    }

}
