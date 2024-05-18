<?php


namespace Pina\Controls\Menu;


use Pina\Access;
use Pina\App;
use Pina\Html;

class LinkMenuItem extends MenuItem
{

    protected $title = '';
    protected $link = '';

    public function load(string $title, string $link)
    {
        $this->title = $title;
        $this->link = $link;
    }

    protected function isPermitted(): bool
    {
        $parsed = parse_url($this->link);

        if (empty($parsed['host']) || $parsed['host'] == App::host()) {
            return Access::isPermitted($parsed['path']);
        }

        return parent::isPermitted();
    }

    protected function drawInner()
    {
        return Html::a($this->title, $this->link, $this->makeAttributes());
    }
}