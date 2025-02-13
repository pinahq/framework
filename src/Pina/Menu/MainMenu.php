<?php


namespace Pina\Menu;

use Pina\Controls\Nav\Nav;

class MainMenu extends Nav
{

    public function merge(Nav $nav)
    {
        $this->innerAfter = array_merge($this->innerAfter, $nav->innerAfter);
        $this->innerBefore = array_merge($this->innerBefore, $nav->innerBefore);
    }

}