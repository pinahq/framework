<?php

namespace Pina\Menu;

use Pina\App;
use Pina\Controls\Control;
use Pina\Controls\Nav\Nav;

class SectionMenuComposer
{
    protected $resources = [];

    public function register($link, SectionMenu $menu)
    {
        $parsed = parse_url($link);
        if (!empty($parsed['host']) && $parsed['host'] != App::host()) {
            return;
        }

        $path = trim($parsed['path'], '/');
        if (strpos($path, '/') !== false) {
            return;
        }

        $this->resources[$path] = $menu;
    }

    public function resolve($resource): Control
    {
        $parts = explode('/', $resource);
        return $this->resources[$parts[0]] ?? App::make(Nav::class);
    }

}