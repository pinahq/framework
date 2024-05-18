<?php


namespace Pina\Controls\Menu;


use Pina\Html;

class NestedMenuItem extends MenuItem
{
    protected $title = '';

    /** @var Menu */
    protected $menu;

    public function load(string $title, Menu $menu)
    {
        $this->title = $title;
        $this->menu = $menu;
        $this->addClass('dropdown');
    }

    protected function drawInner()
    {
        $submenu = $this->drawMenu();
        if (empty($submenu)) {
            return '';
        }
        return Html::a($this->title, '#', $this->makeAttributes()) . $submenu;
    }

    protected function drawMenu()
    {
        $menu = strval($this->menu);
        if (empty($menu)) {
            return '';
        }

        return Html::nest('.dropdown-menu', $menu);
    }
}