<?php

namespace Pina;

use Exception;
use Pina\Http\Endpoint;
use Pina\Http\Request;
use Pina\Model\LinkedItem;
use Pina\Model\LinkedItemCollection;

class Router
{

    protected $endpoints = [];
    protected $patterns = [];
    protected $context = [];

    /**
     * @param string $pattern
     * @param string $class
     */
    public function register($pattern, $class, $context = [])
    {
        $controller = Url::controller($pattern);
        $this->endpoints[$controller] = $class;
        $this->patterns[$controller] = $pattern;
        $this->context[$controller] = $context;
    }

    public function getPatterns()
    {
        return array_values($this->patterns);
    }

    /**
     * @param string $resource
     * @param string $method
     * @return bool
     */
    public function exists($resource, $method)
    {
        list($controller, $action, $params) = Url::route($resource, $method);

        $c = $this->base($controller);

        if (is_null($c)) {
            return false;
        }

        $action .= $this->calcDeeperAction($resource, $this->patterns[$c]);

        return method_exists($this->endpoints[$c], $action);
    }

    public function getEndpointClass($resource): ?string
    {
        $controller = Url::controller($resource);

        $c = $this->base($controller);

        if (is_null($c)) {
            return null;
        }

        return $this->endpoints[$c] ?? null;
    }

    /**
     *
     * @param string $resource
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws Container\NotFoundException
     */
    public function run($resource, $method, $data = [])
    {
        list($controller, $action, $params) = Url::route($resource, $method);

        $c = $this->base($controller);
        if ($c === null) {
            throw new Container\NotFoundException;
        }


        $pattern = $this->patterns[$c];
        $parsed = [];
        $this->parse($resource, $pattern, $parsed);

        $request = new Request($_GET, Input::getData(), array_merge($data, $parsed), $_COOKIE, $_FILES, $_SERVER);

        $location = new Http\Location($resource);

        $controllerCount = count(explode('/', $c));
        $amount = $controllerCount * 2 - 1;

        $baseResource = implode('/', array_slice(explode('/', trim($resource, '/')), 0, $amount));

        $base = new Http\Location($baseResource);

        $request->setLocation($base, $location);

        $request->setContext($this->context[$c]);

        $cl = $this->endpoints[$c];
        /** @var Endpoint $inst */
        $inst = new $cl($request);

        $action .= $this->calcDeeperAction($resource, $pattern);

        if (!method_exists($inst, $action)) {
            throw new NotFoundException();
        }

        return call_user_func_array([$inst, $action], $params);
    }

    public function findChilds(string $resource)
    {
        list($controller, $action, $params) = Url::route($resource, 'get');
        $prefix = $controller . '/';
        $found = [];
        foreach ($this->patterns as $c => $e) {
            if (strpos($c, $prefix) !== 0) {
                continue;
            }
            $right = substr($c, strlen($prefix));
            if (strpos($right, '/') !== false) {
                continue;
            }
            $found[] = $resource . '/' . $right;
        }
        return $found;
    }

    /**
     * @return LinkedItemCollection
     */
    public function getMenu(): LinkedItemCollection
    {
        $menu = new LinkedItemCollection();
        foreach ($this->patterns as $pattern) {
            if (strpos($pattern, '/') !== false) {
                continue;
            }

            $resource = Url::resource($pattern, []);
            if (!Access::isPermitted($resource)) {
                continue;
            }

            try {
                $title = $this->run($resource, 'title');
                if ($title) {
                    $menu->add(new LinkedItem($title, '/' . $resource));
                }
            } catch (Exception $e) {
            }
        }
        return $menu;
    }

    /**
     * @param string $controller
     * @return string|null
     */
    public function base($controller)
    {
        $controller = trim($controller, "/");
        if (!empty($this->endpoints[$controller])) {
            return $controller;
        }

        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i + 1));
            if (isset($this->endpoints[$c])) {
                return $c;
            }
        }
        return null;
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @param array $parsed
     * @return bool
     */
    public function parse($resource, $pattern, &$parsed)
    {
        list($preg, $map) = Url::preg($pattern);
        return $this->pregParse($resource, $preg, $map, $parsed);
    }

    /**
     * @param string $resource
     * @param string $preg
     * @param array $map
     * @param array $parsed
     * @return bool
     */
    public function pregParse($resource, $preg, $map, &$parsed)
    {
        $parsed = [];
        if (preg_match("/^" . $preg . "/si", $resource, $matches)) {
            unset($matches[0]);
            $matches = array_values($matches);
            $parsed = array_combine($map, $matches);
            return true;
        }
        return false;
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @return string
     */
    private function calcDeeperAction($resource, $pattern)
    {
        $deeper = [];
        if ($this->parse($resource, $pattern . "/:id/:__action", $deeper)) {
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
