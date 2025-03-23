<?php

namespace Pina\Menu;

use Pina\App;
use Pina\Controls\Nav\Nav;
use Pina\Controls\Nav\NavItem;

class SectionMenu extends Nav
{

    public function prependLink(string $title, string $link, ?bool $newPage = null): NavItem
    {
        $item = parent::prependLink($title, $link, $newPage);
        $this->register($link);
        return $item;
    }

    public function prependAction(string $title, string $resource, string $method, array $params = []): NavItem
    {
        $item = parent::prependAction($title, $resource, $method, $params);
        $this->register($resource);
        return $item;
    }

    public function appendLink(string $title, string $link, ?bool $newPage = null): NavItem
    {
        $item = parent::appendLink($title, $link, $newPage);
        $this->register($link);
        return $item;
    }

    public function appendAction(string $title, string $resource, string $method, array $params = []): NavItem
    {
        $item = $this->makeAction($title, $resource, $method, $params);
        $this->register($resource);
        return $item;
    }

    public function register($link)
    {
        /** @var SectionMenuComposer $composer */
        $composer = App::load(SectionMenuComposer::class);
        $composer->register($link, $this);
    }

    protected function calcScore($link)
    {
        $score = 0;
        if ($this->location == $link || strpos($this->location, $link .'/') === 0) {
            $score ++;
        }

        $this->scores[$link] = $score;
        if ($score > $this->maxScore) {
            $this->maxScore = $score;
        }
    }

}