<?php


namespace Pina\Menu;

use Pina\Controls\Nav\Nav;

class MainMenu extends Nav
{
    public function merge(Nav $nav)
    {
        $this->inner = array_merge($this->inner, $nav->inner);
    }

}