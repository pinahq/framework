<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Layouts\DefaultLayout;
use Pina\Response;
use Pina\ResponseInterface;

abstract class Control extends AttributedBlock implements ResponseInterface
{

    /**
     * Обёртки контрола
     * @var ControlContainer[]
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
     * Логика отрисовки контрола
     * @return string
     */
    abstract protected function draw(): string;

    /**
     * @deprecated в пользу прямой работы с контейнером или конкретных реализаций контролов
     * @param Control $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        App::container()->set(DefaultLayout::class, $layout);
        return $this;
    }

    /**
     * @return Control
     */
    public function makeLayout()
    {
        return App::make(DefaultLayout::class);
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
     * Обернуть контрол оберткой
     * @param ControlContainer $wrapper
     * @return $this
     */
    public function wrap(ControlContainer $wrapper)
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
            $w->append(new RawHtml($r));
            $r = $w->drawWithWrappers();
            $w->cancelAppend();
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

    public function __invoke()
    {
        return $this->drawWithWrappers();
    }

    public function send()
    {
        $layout = $this->makeLayout();
        $content = $layout->append($this)->drawWithWrappers();
        Response::ok()->setContent($content)->send();
    }

}