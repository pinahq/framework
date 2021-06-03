<?php

namespace Pina\Controls;

abstract class Control extends AttributedBlock
{

    protected $isBuildStarted = false;

    /**
     * @var Control[]
     */
    protected $controls = [];

    /**
     * @var Control[]
     */
    protected $innerAfter = [];

    /**
     * @var Control[]
     */
    protected $innerBefore = [];

    /**
     * @var Control[]
     */
    protected $wrappers = [];

    /**
     * @var Control[]
     */
    protected $after = [];

    /**
     * @var Control[]
     */
    protected $before = [];

    /**
     * @var Control|null
     */
    protected $layout = null;

    /**
     * @return string
     */
    abstract protected function draw();

    /**
     * @param Control $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return Control
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return void
     */
    public function startBuild()
    {
        $this->isBuildStarted = true;
    }

    /**
     * @return bool
     */
    public function isBuildStarted()
    {
        return $this->isBuildStarted;
    }

    /**
     * @param Control $control
     * @return $this
     */
    public function append($control)
    {
        if ($this->isBuildStarted) {
            //пошла сборка контролов по запросу отрисовки
            $this->controls[] = $control;
        } else {
            //запоминаем, какие контролы хотят отрисоваться после основного блока
            $this->innerAfter[] = $control;
        }
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
     * @param Control $control
     */
    public function after($control)
    {
        $this->after[] = $control;
    }

    /**
     * @param Control $control
     */
    public function before($control)
    {
        $this->before[] = $control;
    }

    /**
     * @param Control $wrapper
     * @return $this
     */
    public function wrap($wrapper)
    {
        return $this->pushWrapper($wrapper);
    }

    /**
     * @return $this
     */
    public function unwrap()
    {
        $this->popWrapper();
        return $this;
    }

    /**
     * @param Control $wrapper
     * @return $this
     */
    public function pushWrapper($wrapper)
    {
        array_push($this->wrappers, $wrapper);
        return $this;
    }

    /**
     * @return Control|null
     */
    public function popWrapper()
    {
        return array_pop($this->wrappers);
    }

    /**
     * @return bool
     */
    public function hasWrapper()
    {
        return !empty($this->wrappers);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->innerBefore) + count($this->controls) + count($this->innerAfter);
    }

    /**
     * @return string
     */
    public function compile()
    {
        $r = '';
        foreach ($this->innerBefore as $c) {
            $r .= $c->drawWithWrappers();
        }
        foreach ($this->controls as $c) {
            $r .= $c->drawWithWrappers();
        }
        foreach ($this->innerAfter as $c) {
            $r .= $c->drawWithWrappers();
        }
        return $r;
    }

    /**
     * @return string
     */
    public function drawWithWrappers()
    {
        $r = '';
        foreach ($this->before as $c) {
            $r .= $c->drawWithWrappers();
        }

        $r .= $this->draw();
        foreach ($this->after as $c) {
            $r .= $c->drawWithWrappers();
        }
        foreach ($this->wrappers as $w) {
            $raw = new RawHtml();
            $raw->setText($r);
            array_push($w->controls, $raw);
            $r = $w->drawWithWrappers();
            array_pop($w->controls);
        }

        return $r;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->drawWithWrappers();
    }


}
