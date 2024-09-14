<?php


namespace Pina\Controls\Nav;


use Pina\Access;
use Pina\App;
use Pina\Html;

class LinkNavItem extends NavItem
{

    protected $link = '';

    public function load(string $title, string $link)
    {
        $this->title = $title;
        $this->link = $link;
    }

    public function getLink()
    {
        return $this->link;
    }

    protected function isPermitted(): bool
    {
        $parsed = parse_url($this->link);

        if (empty($parsed['host']) || $parsed['host'] == App::host()) {
            return Access::isPermitted($parsed['path']);
        }

        return true;
    }

    protected function draw()
    {
        if (!$this->isPermitted()) {
            return '';
        }

        $inner = $this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter();

        return Html::li(Html::a($inner, $this->link, $this->makeLinkAttributes()), $this->makeAttributes());
    }

    protected function drawInner()
    {
        return $this->title;
    }

    protected function makeLinkAttributes()
    {
        $options = [];
        if ($this->isExternalLink()) {
            $options['target'] = '_blank';
        }
        return $options;
    }

    protected function isExternalLink()
    {
        $host = parse_url($this->link, PHP_URL_HOST);
        return !empty($host) && $host != App::host();
    }

}