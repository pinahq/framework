<?php

namespace Pina\Controls\Menu;

use Pina\App;
use Pina\Controls\Control;
use Pina\Html;

class Menu extends Control
{

    protected $list = [];


    protected function draw()
    {
        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();
        if (empty($inner)) {
            return '';
        }

        return Html::tag(
            'ul',
            $inner,
            $this->makeAttributes(['class' => 'nav'])
        );
    }

    public function prependLink(string $title, string $link)
    {
        $this->prepend($this->makeLink($title, $link));
    }

    public function prependAction(string $title, string $resource, string $method, array $params = [])
    {
        $this->prepend($this->makeAction($title, $resource, $method, $params));
    }

    public function prependNested(string $title, Menu $menu)
    {
        $this->prepend($this->makeNested($title, $menu));
    }

    public function appendLink(string $title, string $link)
    {
        $this->append($this->makeLink($title, $link));
    }

    public function appendAction(string $title, string $resource, string $method, array $params = [])
    {
        $this->append($this->makeAction($title, $resource, $method, $params));
    }

    public function appendNested(string $title, Menu $menu)
    {
        $this->append($this->makeNested($title, $menu));
    }

    protected function makeLink(string $title, string $link): LinkMenuItem
    {
        /** @var LinkMenuItem $item */
        $item = App::make(LinkMenuItem::class);
        $item->load($title, $link);
        return $item;
    }

    protected function makeAction(string $title, string $resource, string $method, array $params = [])
    {
        /** @var ActionMenuItem $item */
        $item = App::make(ActionMenuItem::class);
        $item->load($title, $resource, $method, $params);
        return $item;
    }

    protected function makeNested(string $title, Menu $menu)
    {
        /** @var NestedMenuItem $item */
        $item = App::make(NestedMenuItem::class);
        $item->load($title, $menu);
        return $item;
    }


}