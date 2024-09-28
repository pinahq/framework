<?php


namespace Pina\Controls\Nav;


use Pina\Access;
use Pina\App;
use Pina\Html;

class LinkNavItem extends NavItem
{

    protected $link = '';
    protected $newPage = null;

    public function load(string $title, string $link, ?bool $newPage = null)
    {
        $this->title = $title;
        $this->link = $link;
        $this->newPage = $newPage;
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
        if (!is_null($this->newPage) && $this->newPage) {
            return true;
        }

        $host = parse_url($this->link, PHP_URL_HOST);
        return !empty($host) && $host != App::host();
    }

}