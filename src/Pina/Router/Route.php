<?php

namespace Pina\Router;

use Pina\Access;
use Pina\App;
use Pina\Controls\Nav\Nav;
use Pina\CSRF;
use Pina\Http\Endpoint;
use Pina\Http\Request;
use Pina\Url;

class Route
{

    protected $pattern = '';
    protected $endpoint = '';
    protected $context = [];
    protected $tags = [];

    public function __construct(string $pattern, string $class, array $context = [])
    {
        $this->pattern = $pattern;
        $this->endpoint = $class;
        $this->context = $context;
    }

    public function getController()
    {
        return Url::controller($this->pattern);
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function makeEndpoint(): Endpoint
    {
        $cl = $this->endpoint;
        return new $cl();
    }

    public function permit($groups)
    {
        App::access()->permit($this->pattern, $groups);
        return $this;
    }

    public function clearPermission()
    {
        App::access()->clear($this->pattern);
        return $this;
    }

    public function ignoreCSRF()
    {
        $controller = URL::controller($this->pattern);
        CSRF::whitelist([$controller]);
        return $this;
    }

    public function addToMenu(Nav ...$menus)
    {
        $endpoint = $this->makeEndpoint(new Request());
        if (!method_exists($endpoint, 'title')) {
            return $this;
        }

        $title = $endpoint->title(0);
        if (empty($title)) {
            return $this;
        }

        $badges = [];
        if (method_exists($endpoint, 'badges')) {
            $badges = $endpoint->badges(0);
        }

        foreach ($menus as $menu) {
            $menuItem = $menu->appendLink($title, App::link($this->pattern));
            foreach ($badges as $badge) {
                $menuItem->append($badge);
            }
        }
        return $this;
    }

    public function addTag($tag)
    {
        $this->tags[] = $tag;
        return $this;
    }

    public function hasTag($tag): bool
    {
        return in_array($tag, $this->tags);
    }

}