<?php


namespace Pina\Controls\Nav;

use Pina\Html;

class DropdownNavItem extends LinkNavItem
{
    protected $title = '';

    /** @var Nav */
    protected $dropdown;

    public function setDropdown(Nav $dropdown)
    {
        $this->dropdown = $dropdown;
        $this->addClass('dropdown');
    }

    protected function isPermitted(): bool
    {
        return true;
    }

    protected function draw()
    {
        $submenu = $this->drawDropdown();
        if (empty($submenu)) {
            return '';
        }

        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();

        return Html::li(Html::a($inner, $this->link, $this->makeLinkAttributes()) . $submenu, $this->makeAttributes());
    }

    protected function drawDropdown()
    {
        if (!isset($this->dropdown)) {
            return '';
        }

        $dropdown = strval($this->dropdown);
        if (empty($dropdown)) {
            return '';
        }

        return Html::nest('.dropdown-menu', $dropdown);
    }

}