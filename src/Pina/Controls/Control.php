<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Layouts\DefaultLayout;
use Pina\ResponseInterface;

abstract class Control extends AttributedBlock implements ResponseInterface
{

    /**
     * Обёртки контрола
     * @var Control[]
     */
    protected $wrappers = [];

    /**
     * Элементы, располагающиеся за данным контролом
     * @var Control[]
     */
    protected $after = [];

    /**
     * Элементы, располагающиеся перед данным контролом
     * @var Control[]
     */
    protected $before = [];

    /**
     * Элементы, располагающиеся внутри контейнера данного контрола после внутреннего контента
     * @var Control[]
     */
    protected $innerAfter = [];

    /**
     * Элементы, располагающиеся внутри контейнера данного контрола до внутреннего контента
     * @var Control[]
     */
    protected $innerBefore = [];

    /**
     * @var Control|null
     */
    protected $layout = null;

    /**
     * Логика отрисовки контрола
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
     * Добавить элемент после
     * @param Control $control
     * @return $this
     */
    public function after($control)
    {
        $this->after[] = $control;
        return $this;
    }

    /**
     * Добавить элемент до
     * @param Control $control
     * @return $this
     */
    public function before($control)
    {
        $this->before[] = $control;
        return $this;
    }

    /**
     * Добавить элемент внутри контейнера после основного контента
     * @param Control $control
     * @return $this
     */
    public function append($control)
    {
        $this->innerAfter[] = $control;
        return $this;
    }

    /**
     * Добавить элемент внутри контейна до основного контента
     * @param Control $control
     * @return $this
     */
    public function prepend($control)
    {
        array_unshift($this->innerBefore, $control);
        return $this;
    }


    /**
     * Обернуть контрол оберткой
     * @param Control $wrapper
     * @return $this
     */
    public function wrap($wrapper)
    {
        return $this->pushWrapper($wrapper);
    }

    /**
     * Снять внешнюю обертку с контрола
     * @return $this
     */
    public function unwrap()
    {
        $this->popWrapper();
        return $this;
    }

    /**
     * Обернуть контрол оберткой
     * @param Control $wrapper
     * @return $this
     */
    public function pushWrapper($wrapper)
    {
        array_push($this->wrappers, $wrapper);
        return $this;
    }

    /**
     * Снять внешнюю обертку с контрола и получить ее
     * @return Control|null
     */
    public function popWrapper()
    {
        return array_pop($this->wrappers);
    }

    /**
     * Проверить, есть ли еще обертки у контрола
     * @return bool
     */
    public function hasWrapper()
    {
        return !empty($this->wrappers);
    }

    /**
     * Отрисовать контрол вместе с обертками и связанными элементами
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
     * Отрисовать связанные элементы внутри контейнера до основного контента
     * @param Control $wrapper Если нужно каждый элемент обернуть в другой контрол, то он передается в параметре
     * @return string
     */
    protected function drawInnerBefore(Control $wrapper = null)
    {
        $r = '';
        foreach ($this->innerBefore as $c) {
            if ($wrapper) {
                $r .= (clone $wrapper)->append($c);
            } else {
                $r .= $c;
            }
        }
        return $r;
    }

    /**
     * Отрисовать основной контент
     * @return string
     */
    protected function drawInner()
    {
        return '';
    }

    /**
     * Отрисовать связанные элементы внутри контейнера после основного контента
     * @param Control $wrapper Если нужно каждый элемент обернуть в другой контрол, то он передается в параметре
     * @return string
     */
    protected function drawInnerAfter(Control $wrapper = null)
    {
        $r = '';
        foreach ($this->innerAfter as $c) {
            if ($wrapper) {
                $r .=  (clone $wrapper)->append($c);
            } else {
                $r .= $c;
            }
        }
        return $r;
    }

    /**
     * Преобразовать в строку путем отрисовки контрола вместе с обертками и связанными элементами
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
