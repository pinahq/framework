<?php

namespace Pina\Router;

use Pina\App;
use Pina\Controls\Nav\Nav;
use Pina\CSRF;
use Pina\Http\Endpoint;
use Pina\NotFoundException;
use Pina\ResponseInterface;
use Pina\Url;

class Route
{

    protected $pattern = '';
    protected $endpoint = '';

    public function __construct(string $pattern, string $class)
    {
        $this->pattern = $pattern;
        $this->endpoint = $class;
    }

    public function getController()
    {
        return Url::controller($this->pattern);
    }

    public function getPattern()
    {
        return $this->pattern;
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
        $endpoint = $this->makeEndpoint();
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
                $menuItem->setBadge($badge);
            }
        }
        return $this;
    }

    public function exists($action)
    {
        return method_exists($this->makeEndpoint(), $action);
    }

    public function run($action, $params)
    {
        $r = $this->call($action, $params);
        if ($r) {
            if ($r instanceof ResponseInterface) {
                $r->send();
            }
        } else {
            throw new NotFoundException();
        }
    }

    public function call($action, $params = [])
    {
        $inst = $this->makeEndpoint();
        if (!method_exists($inst, $action)) {
            return null;
        }
        $r = call_user_func_array([$inst, $action], $params);
        if ($r) {
            return $r;
        }

        return null;
    }

}