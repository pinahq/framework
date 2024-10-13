<?php

namespace Pina\Controls\Nav;

use Pina\App;
use Pina\Controls\Control;
use Pina\Html;
use Pina\Url;

class Nav extends Control
{

    protected $scores = [];
    protected $maxScore = 0;

    protected $location = '';

    public function __construct()
    {
        $this->location = App::link(App::resource());
    }

    public function setLocation(string $location)
    {
        $this->location = $location;
    }

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

    protected function drawInnerAfter(Control $wrapper = null)
    {
        $this->processActive($this->innerAfter);

        return parent::drawInnerAfter($wrapper);
    }

    protected function drawInnerBefore(Control $wrapper = null)
    {
        $this->processActive($this->innerBefore);

        return parent::drawInnerBefore($wrapper);
    }

    /**
     * @param Control[] $controls
     * @return void
     */
    protected function processActive(array $controls)
    {
        foreach ($controls as $control) {
            if (!method_exists($control, 'getLink')) {
                continue;
            }

            /** @var LinkNavItem $control */
            $link = $control->getLink();
            if (empty($this->scores[$link])) {
                continue;
            }
            $score = $this->scores[$link] ?? 0;
            if ($score == $this->maxScore) {
                $control->addClass('active');
            } else {
                $control->removeClass('active');
            }
        }
    }

    public function prependLink(string $title, string $link, ?bool $newPage = null): NavItem
    {
        $item = $this->makeLink($title, $link, $newPage);
        $this->calcScore($link);
        $this->prepend($item);
        return $item;
    }

    public function prependAction(string $title, string $resource, string $method, array $params = []): NavItem
    {
        $item = $this->makeAction($title, $resource, $method, $params);
        $this->prepend($item);
        return $item;
    }

    public function prependDropdown(string $title, Nav $menu): NavItem
    {
        $item = $this->makeDropdown($title, $menu);
        $this->prepend($item);
        return $item;
    }

    public function appendLink(string $title, string $link, ?bool $newPage = null): NavItem
    {
        $item = $this->makeLink($title, $link, $newPage);
        $this->calcScore($link);
        $this->append($item);
        return $item;
    }

    public function appendAction(string $title, string $resource, string $method, array $params = []): NavItem
    {
        $item = $this->makeAction($title, $resource, $method, $params);
        $this->append($item);
        return $item;
    }

    public function appendDropdown(string $title, Nav $menu)
    {
        $this->append($this->makeDropdown($title, $menu));
    }

    protected function makeLink(string $title, string $link, ?bool $newPage = null): LinkNavItem
    {
        /** @var LinkNavItem $item */
        $item = App::make(LinkNavItem::class);
        $item->load($title, $link, $newPage);
        if ($newPage) {
            $item->setNewPage();
        }
        return $item;
    }

    protected function makeAction(string $title, string $resource, string $method, array $params = []): ActionNavItem
    {
        /** @var ActionNavItem $item */
        $item = App::make(ActionNavItem::class);
        $item->load($title, $resource, $method, $params);
        return $item;
    }

    protected function makeDropdown(string $title, Nav $menu)
    {
        /** @var DropdownNavItem $item */
        $item = App::make(DropdownNavItem::class);
        $item->load($title, '#');
        $item->setDropdown($menu);
        return $item;
    }

    protected function calcScore($link)
    {
        $score = Url::nestedWeight($link, $this->location);

        $this->scores[$link] = $score;
        if ($score > $this->maxScore) {
            $this->maxScore = $score;
        }
    }


}