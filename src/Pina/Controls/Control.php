<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Layouts\DefaultLayout;
use Pina\ResponseInterface;

abstract class Control extends AttributedBlock implements ResponseInterface
{

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
        return is_null($this->layout) ? App::make(DefaultLayout::class) : $this->layout;
    }

    /**
     * @param Control $control
     * @return $this
     */
    public function after($control)
    {
        $this->after[] = $control;
        return $this;
    }

    /**
     * @param Control $control
     * @return $this
     */
    public function before($control)
    {
        $this->before[] = $control;
        return $this;
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
            array_push($w->innerAfter, $raw);
            $r = $w->drawWithWrappers();
            array_pop($w->innerAfter);
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

    public function send()
    {
        header('HTTP/1.1 200 OK');
        header('Content-Type: text/html');
        echo $this->__toString();
    }


}
