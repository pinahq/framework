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

    public function prependLink(string $title, string $link): MenuItem
    {
        $item = $this->makeLink($title, $link);
        $this->prepend($item);
        return $item;
    }

    public function prependAction(string $title, string $resource, string $method, array $params = []): MenuItem
    {
        $item = $this->makeAction($title, $resource, $method, $params);
        $this->prepend($item);
        return $item;
    }

    public function prependNested(string $title, Menu $menu): MenuItem
    {
        $item = $this->makeNested($title, $menu);
        $this->prepend($item);
        return $item;
    }

    public function appendLink(string $title, string $link): MenuItem
    {
        $item = $this->makeLink($title, $link);
        $this->append($item);
        return $item;
    }

    public function appendAction(string $title, string $resource, string $method, array $params = []): MenuItem
    {
        $item = $this->makeAction($title, $resource, $method, $params);
        $this->append($item);
        return $item;
    }

    public function appendNested(string $title, Menu $menu): MenuItem
    {
        $item = $this->makeNested($title, $menu);
        $this->append($item);
        return $item;
    }

    protected function makeLink(string $title, string $link): LinkMenuItem
    {
        /** @var LinkMenuItem $item */
        $item = App::make(LinkMenuItem::class);
        $item->load($title, $link);
        return $item;
    }

    protected function makeAction(string $title, string $resource, string $method, array $params = []): ActionMenuItem
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