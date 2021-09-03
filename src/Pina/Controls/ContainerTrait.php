<?php


namespace Pina\Controls;


trait ContainerTrait
{
    /**
     * @var Control[]
     */
    protected $innerAfter = [];

    /**
     * @var Control[]
     */
    protected $innerBefore = [];

    /**
     * @param Control $control
     * @return $this
     */
    public function append($control)
    {
        $this->innerAfter[] = $control;
        return $this;
    }

    /**
     * @param Control $control
     * @return $this
     */
    public function prepend($control)
    {
        array_unshift($this->innerBefore, $control);
        return $this;
    }

    /**
     * @return string
     */
    protected function drawInnerBefore()
    {
        $r = '';
        foreach ($this->innerBefore as $c) {
            $r .= $c->drawWithWrappers();
        }
        return $r;
    }

    /**
     * @return string
     */
    protected function drawInnerAfter()
    {
        $r = '';
        foreach ($this->innerAfter as $c) {
            $r .= $c->drawWithWrappers();
        }
        return $r;
    }


}