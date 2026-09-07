<?php

namespace Pina\Router;

use Pina\App;

class RouteLocator
{
    protected Route $route;
    protected RouteGroup $group;

    public function __construct(Route $route, RouteGroup $group)
    {
        $this->route = $route;
        $this->group = $group;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

    public function callRoute($action, $params)
    {
        $r = null;
        App::call($this->group->getContainer(), function() use ($action, $params, &$r) {
            $r = $this->route->call($action, $this->resolveParams($params, $this->route->getController()));
        });
        return $r;
    }

    public function runRoute($action, $params)
    {
        App::call($this->group->getContainer(), function() use ($action, $params) {
            $this->route->run($action, $this->resolveParams($params, $this->route->getController()));
        });
    }

    protected function resolveParams($params, $c)
    {
        $parts = count(explode('/', $c));
        $offset = $parts - 1;
        return array_slice(array_reverse(array_values($params)), $offset);
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @return string
     */
    public function calcDeeperAction($resource)
    {
        $deeper = [];
        if ($this->group->parse($resource, $this->route->getPattern() . "/:id/:__action", $deeper)) {
            $deeperAction = pathinfo($deeper['__action'], PATHINFO_FILENAME);

            return $this->ucfirstEveryWord($deeperAction);
        }

        return '';
    }

    /**
     * @param string $s
     * @return string
     */
    private function ucfirstEveryWord($s)
    {
        $parts = preg_split("/[^\w]/s", $s);
        foreach ($parts as $k => $v) {
            $parts[$k] = ucfirst($v);
        }
        return implode($parts);
    }

}